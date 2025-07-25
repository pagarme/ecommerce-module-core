<?php

namespace Pagarme\Core\Webhook\Services;

use Exception;
use Pagarme\Core\Kernel\Services\LogService;
use stdClass;

class WebhookValidatorService
{
    const JWKS_URL = 'https://hubapi.pagar.me/.well-known/jwks.json';
    const DEFAULT_ALGORITHM = 'RS256';
    const DEFAULT_KTY_TYPE = 'RSA';
    const DEFAULT_USE = 'sig';

    /**
     * Validates the signature of a webhook payload.
     * Assume alg=RS256 and get the JWKS from an external URL.
     *
     * @param string $payloadJson The RAW (JSON) body of the request.
     * @param string $signatureHeader The full signature header value (e.g., "alg=RS256; kid=...; signature=...").
     * @return bool True if the signature is valid, false otherwise.
     */
    public static function validateSignature(string $payloadJson, string $signatureHeader): bool
    {
        $logService = new LogService(
            'Webhook',
            true
        );

        $headerParts = self::parseSignatureHeader($signatureHeader);
        if (!isset($headerParts['alg'], $headerParts['kid'], $headerParts['signature'])) {
            $e = new Exception("Invalid signature header: Missing alg, kid, or signature.");
            $logService->exception($e);
            return false;
        }
        $alg = $headerParts['alg'];
        $kid = $headerParts['kid'];
        $receivedSignatureB64 = $headerParts['signature'];

        if ($alg !== self::DEFAULT_ALGORITHM) {
            $e = new Exception("Unsupported algorithm: {$alg}. Expected " . self::DEFAULT_ALGORITHM . ".");
            $logService->exception($e);
            return false;
        }

        $jwksUrl = self::JWKS_URL;
        $jwksData = self::fetchAndParseJwks($jwksUrl);
        if ($jwksData === null) {
            $e = new Exception("Failed to fetch or parse JWKS from {$jwksUrl}.");
            $logService->exception($e);
            return false;
        }

        $publicKeyJwk = self::findJwkInJwks($jwksData, $kid, $alg);
        if ($publicKeyJwk === null) {
            $e = new Exception("Public key with KID '{$kid}' and ALG '{$alg}' not found or invalid in JWKS.");
            $logService->exception($e);
            return false;
        }

        $pemPublicKey = self::createPemFromModulusAndExponent($publicKeyJwk->n, $publicKeyJwk->e);
        if ($pemPublicKey === null) {
            $e = new Exception("Failed to construct PEM public key from JWK components.");
            $logService->exception($e);
            return false;
        }

        $decodedSignature = self::base64UrlDecode($receivedSignatureB64);
        $isValid = openssl_verify(
            $payloadJson,
            $decodedSignature,
            $pemPublicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($isValid === -1) {
            $e = new Exception("OpenSSL verification error: " . openssl_error_string());
            $logService->exception($e);
            return false;
        }

        return (bool)$isValid;
    }

    /**
     * Parse the signature header.
     * @param string $headerValue
     * @return array Associative array of parsed parts.
     */
    private static function parseSignatureHeader(string $headerValue): array
    {
        $result = [];
        foreach (explode(';', $headerValue) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                $result[$kv[0]] = $kv[1];
            }
        }
        return $result;
    }

    /**
     * Fetches JWKS from a URL and parses it.
     * @param string $jwksUrl
     * @return stdClass|null JWKS data as an object, or null on failure.
     */
    private static function fetchAndParseJwks(string $jwksUrl): ?stdClass
    {
        $jwksJson = @file_get_contents($jwksUrl);
        if ($jwksJson === false) {
            return null;
        }
        $jwksData = json_decode($jwksJson, false);
        if ($jwksData === null || !isset($jwksData->keys) || !is_array($jwksData->keys)) {
            return null;
        }
        return $jwksData;
    }

    /**
     * Find a specific JWK in JWKS.
     * @param stdClass $jwksData
     * @param string $kid
     * @param string $alg
     * @return stdClass|null The JWK object, or null if not found/invalid.
     */
    private static function findJwkInJwks(stdClass $jwksData, string $kid, string $alg): ?stdClass
    {
        foreach ($jwksData->keys as $key) {
            if (
                isset($key->kid) && $key->kid === $kid
                && isset($key->kty) && $key->kty === self::DEFAULT_KTY_TYPE
                && isset($key->use) && $key->use === self::DEFAULT_USE
                && (!isset($key->alg) || $key->alg === $alg)
            ) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Decodes a Base64Url string.
     * @param string $base64url
     * @return string
     */
    private static function base64UrlDecode(string $base64url): string
    {
        $base64 = strtr($base64url, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding > 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($base64);
    }

    /**
     * Converts the binary modulus (n) and exponent (e) components of
     * an RSA key to the public key PEM format (SubjectPublicKeyInfo).
     *
     * @param string $nBase64Url RSA module in Base64Url format.
     * @param string $eBase64Url RSA exponent in Base64Url format.
     * @return string|null The public key in PEM format or null in case of error.
     */
    public static function createPemFromModulusAndExponent(string $nBase64Url, string $eBase64Url): ?string
    {
        $modulus = self::base64UrlDecode($nBase64Url);
        $exponent = self::base64UrlDecode($eBase64Url);

        if (!$modulus || !$exponent) return null;

        $modulus = ltrim($modulus, "\x00");
        $exponent = ltrim($exponent, "\x00");

        $rsaPublicKey = self::asn1EncodeSequence(
            self::asn1EncodeInteger($modulus) . self::asn1EncodeInteger($exponent)
        );

        $algorithmIdentifier = self::asn1EncodeSequence(
            self::asn1EncodeOID('1.2.840.113549.1.1.1') . self::asn1EncodeNull()
        );

        $subjectPublicKeyInfo = self::asn1EncodeSequence(
            $algorithmIdentifier . self::asn1EncodeBitString($rsaPublicKey)
        );

        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n") .
            "-----END PUBLIC KEY-----\n";
    }

    private static function asn1EncodeLength(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $temp = ltrim(pack("N", $length), "\x00");
        return chr(0x80 | strlen($temp)) . $temp;
    }

    private static function asn1EncodeInteger(string $bytes): string
    {
        if (ord($bytes[0]) > 0x7F) {
            $bytes = "\x00" . $bytes;
        }

        return "\x02" . self::asn1EncodeLength(strlen($bytes)) . $bytes;
    }

    private static function asn1EncodeSequence(string $data): string
    {
        return "\x30" . self::asn1EncodeLength(strlen($data)) . $data;
    }

    private static function asn1EncodeBitString(string $data): string
    {
        return "\x03" . self::asn1EncodeLength(strlen($data) + 1) . "\x00" . $data;
    }

    private static function asn1EncodeNull(): string
    {
        return "\x05\x00";
    }

    private static function asn1EncodeOID(string $oid): string
    {
        $parts = explode('.', $oid);
        $first = 40 * (int)$parts[0] + (int)$parts[1];
        $rest = array_slice($parts, 2);

        $encoded = chr($first);
        foreach ($rest as $part) {
            $val = (int)$part;
            $chunk = '';
            do {
                $chunk = chr($val & 0x7F | 0x80) . $chunk;
                $val >>= 7;
            } while ($val > 0);
            $chunk[strlen($chunk) - 1] = $chunk[strlen($chunk) - 1] & chr(0x7F);
            $encoded .= $chunk;
        }

        return "\x06" . self::asn1EncodeLength(strlen($encoded)) . $encoded;
    }
}
