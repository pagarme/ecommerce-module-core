<?php

namespace Pagarme\Core\Webhook\Services;

use Pagarme\Core\Kernel\Services\LogService;

class WebhookValidatorService
{
    const JWKS_URL = 'https://hubapi.stg.pagar.me/.well-known/jwks.json';
    const DEFAULT_ALGORITHM = 'RS256';
    const DEFAULT_KTY_TYPE = 'RSA';
    const DEFAULT_USE = 'sig';
    const RSA_ENCRYPTION_OID = '1.2.840.113549.1.1.1';

    /**
     * Validates the signature of a webhook payload.
     * Assume alg=RS256 and get the JWKS from an external URL.
     *
     * @param string $payloadJson The RAW (JSON) body of the request.
     * @param string $signatureHeader The full signature header value (e.g., "alg=RS256; kid=...; signature=...").
     * @param string $jwksUrl The JWKS endpoint URL.
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
            $logService->exception("Invalid signature header: Missing alg, kid, or signature.");
            return false;
        }
        $alg = $headerParts['alg'];
        $kid = $headerParts['kid'];
        $receivedSignatureB64 = $headerParts['signature'];

        if ($alg !== self::DEFAULT_ALGORITHM) {
            $logService->exception("Unsupported algorithm: {$alg}. Expected " . self::DEFAULT_ALGORITHM . ".");
            return false;
        }

        $jwksUrl = self::JWKS_URL;
        $jwksData = self::fetchAndParseJwks($jwksUrl);
        if ($jwksData === null) {
            $logService->exception("Failed to fetch or parse JWKS from {$jwksUrl}.");
            return false;
        }

        $publicKeyJwk = self::findJwkInJwks($jwksData, $kid, $alg);
        if ($publicKeyJwk === null) {
            $logService->exception("Public key with KID '{$kid}' and ALG '{$alg}' not found or invalid in JWKS.");
            return false;
        }

        $pemPublicKey = self::createRsaPublicKeyPemFromNandE($publicKeyJwk->n, $publicKeyJwk->e);
        if ($pemPublicKey === null) {
            $logService->exception("Failed to construct PEM public key from JWK components.");
            return false;
        }

        $decodedSignature = self::base64UrlDecode($receivedSignatureB64);
        $isValid = openssl_verify($payloadJson, $decodedSignature, $pemPublicKey, OPENSSL_ALGO_SHA256);

        if ($isValid === -1) {
            $logService->exception("OpenSSL verification error: " . openssl_error_string());
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
     * @param string $data
     * @return string
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Converts the binary modulus (n) and exponent (e) components of
     * an RSA key to the public key PEM format (SubjectPublicKeyInfo).
     *
     * @param string $nBase64Url RSA module in Base64Url format.
     * @param string $eBase64Url RSA exponent in Base64Url format.
     * @return string|null The public key in PEM format or null in case of error.
     */
    private static function createRsaPublicKeyPemFromNandE(string $nBase64Url, string $eBase64Url): ?string
    {
        $n = self::base64UrlDecode($nBase64Url);
        $e = self::base64UrlDecode($eBase64Url);

        $n = ltrim($n, "\0");
        $e = ltrim($e, "\0");

        $components = [];
        $components[] = self::encodeAsn1Integer($n);
        $components[] = self::encodeAsn1Integer($e);
        $rsaPublicKey = self::encodeAsn1Sequence(implode('', $components));

        $algorithmIdentifier = self::encodeAsn1Sequence(
            self::encodeAsn1ObjectIdentifier(self::RSA_ENCRYPTION_OID) . self::encodeAsn1Null()
        );

        $publicKeyBitString = self::encodeAsn1BitString($rsaPublicKey);

        $subjectPublicKeyInfo = self::encodeAsn1Sequence(
            $algorithmIdentifier .
            $publicKeyBitString
        );

        $pem = "-----BEGIN PUBLIC KEY-----\n";
        $pem .= chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n");
        $pem .= "-----END PUBLIC KEY-----\n";

        return $pem;
    }

    private static function encodeAsn1Integer(string $bytes): string
    {
        $len = strlen($bytes);
        if (ord($bytes[0]) & 0x80) { $bytes = "\0" . $bytes; $len++; }
        return "\x02" . self::encodeAsn1Length($len) . $bytes;
    }

    private static function encodeAsn1Sequence(string $bytes): string
    {
        return "\x30" . self::encodeAsn1Length(strlen($bytes)) . $bytes;
    }

    private static function encodeAsn1ObjectIdentifier(string $oid): string
    {
        $parts = explode('.', $oid);
        $bytes = chr(40 * $parts[0] + $parts[1]);
        for ($i = 2; $i < count($parts); $i++) {
            $val = (int)$parts[$i];
            $temp = [];
            do {
                $temp[] = chr(0x80 | ($val & 0x7F));
                $val >>= 7;
            } while ($val > 0);
            $bytes .= implode('', array_reverse($temp));
        }
        return "\x06" . self::encodeAsn1Length(strlen($bytes)) . $bytes;
    }

    private static function encodeAsn1Null(): string
    {
        return "\x05\x00";
    }

    private static function encodeAsn1BitString(string $bytes): string
    {
        return "\x03" . self::encodeAsn1Length(strlen($bytes) + 1) . "\x00" . $bytes;
    }

    private static function encodeAsn1Length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }
        $temp = '';
        while ($length > 0) {
            $temp = chr($length & 0xFF) . $temp;
            $length >>= 8;
        }
        return chr(0x80 | strlen($temp)) . $temp;
    }
}
