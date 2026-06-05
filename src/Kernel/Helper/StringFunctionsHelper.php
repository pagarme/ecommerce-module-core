<?php

namespace Pagarme\Core\Kernel\Helper;

class StringFunctionsHelper
{
    /**
     * @var array<string, string>
     */
    private $unwanted = [
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'Ae',
        'Å' => 'A',
        'Æ' => 'A',
        'Ă' => 'A',
        'Ą' => 'A',
        'ą' => 'a',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'ae',
        'å' => 'a',
        'ă' => 'a',
        'æ' => 'ae',
        'þ' => 'b',
        'Þ' => 'B',
        'Ç' => 'C',
        'ç' => 'c',
        'Ć' => 'C',
        'ć' => 'c',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ę' => 'E',
        'ę' => 'e',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'Ğ' => 'G',
        'ğ' => 'g',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'İ' => 'I',
        'ı' => 'i',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'Ł' => 'L',
        'ł' => 'l',
        'Ñ' => 'N',
        'Ń' => 'N',
        'ń' => 'n',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'Oe',
        'Ø' => 'O',
        'ö' => 'oe',
        'ø' => 'o',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'Š' => 'S',
        'š' => 's',
        'Ş' => 'S',
        'ș' => 's',
        'Ș' => 'S',
        'ş' => 's',
        'ß' => 'ss',
        'Ś' => 'S',
        'ś' => 's',
        'ț' => 't',
        'Ț' => 'T',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'Ue',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'ue',
        'Ý' => 'Y',
        'ý' => 'y',
        'ÿ' => 'y',
        'Ž' => 'Z',
        'ž' => 'z',
        'Ż' => 'Z',
        'ż' => 'z',
        'Ź' => 'Z',
        'ź' => 'z',
    ];

    /**
     * @var array<string, string>
     */
    private $specialCharacters = [
        '!' => '',
        '@' => '',
        '#' => '',
        '$' => '',
        '%' => '',
        '&' => '',
        '*' => '',
    ];

    /**
     * This method will remove all accentiation of your string
     *
     * @param string $str
     * @return string
     */
    final public function removeSpecialCharacters($str)
    {
        $str = strtr($str, $this->unwanted);
        $str = strtr($str, $this->specialCharacters);

        return preg_replace(
            "/[^a-zA-Z ]/",
            '',
            $str ?? ''
        );
    }

    /**
     * @param string $str
     * @return string
     */
    public function cleanStrToDb($str)
    {
        $str = strtr($str, $this->specialCharacters);

        return str_replace(
            "'",
            "`",
            strip_tags($str ?? '')
        );
    }

    /**
     * @param string $text
     * @return string
     */
    public static function removeLineBreaks($text)
    {
        if ($text === null) {
            return "";
        }

        $pattern = '/\\\s+\\\s\\\r\\\n|\\\r|\\\t|\\\n\\\r|\\\n/m';
        $textCleanBreakLines = trim(
            preg_replace(
                $pattern,
                ' ',
                $text ?? ''
            )
        );

        return str_replace(chr(9), '', $textCleanBreakLines);
    }

    /**
     * Applies $fn recursively to every string leaf in $value.
     *
     * - string  → $fn is applied directly.
     * - array   → each value is processed recursively; original keys are preserved.
     * - object  → the object is cloned and each public property is processed recursively.
     * - other   → returned unchanged (int, float, bool, null).
     *
     * @param callable $fn
     * @param mixed    $value
     * @return mixed
     */
    private function applyRecursive(callable $fn, $value)
    {
        if (is_string($value)) {
            return $fn($value);
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->applyRecursive($fn, $item);
            }
            return $result;
        }

        if (is_object($value)) {
            $clone = clone $value;
            foreach (get_object_vars($value) as $property => $item) {
                $clone->$property = $this->applyRecursive($fn, $item);
            }
            return $clone;
        }

        return $value;
    }

    /**
     * Applies cleanStrToDb() recursively to all string values in $value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function cleanRecursive($value)
    {
        return $this->applyRecursive(
            function ($str) {
                return $this->cleanStrToDb($str);
            },
            $value
        );
    }

    /**
     * Applies removeLineBreaks() recursively to all string values in $value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function removeLineBreaksRecursive($value)
    {
        return $this->applyRecursive(
            function ($str) {
                return self::removeLineBreaks($str);
            },
            $value
        );
    }

    /**
     * Applies removeSpecialCharacters() recursively to all string values in $value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function removeSpecialCharactersRecursive($value)
    {
        return $this->applyRecursive(
            function ($str) {
                return $this->removeSpecialCharacters($str);
            },
            $value
        );
    }
}
