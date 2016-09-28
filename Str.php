<?php namespace Mreschke\Helpers;

/**
 * String helpers
 * Class nams is Str because String is a reserved word in PHP 7+
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Str
{

    /**
     * Generate a new v4 36 (or 38 with brackets) char GUID.
     * Ex: 9778d799-b37b-7bfc-2685-47b3d28aa7af
     * @param bool $includeBrackets
     * @return string v4 character guid
     */
    public static function getGuid($includeBrackets = false)
    {
        if (function_exists('com_create_guid')) {
            //If on a windows platform use Windows COM
            if ($includeBrackets) {
                return com_create_guid();
            } else {
                return trim(com_create_guid(), '{}');
            }
        } else {
            //If on a *nix platform, build v4 GUID using PHP
            mt_srand((double)microtime()*10000);
            $charid = md5(uniqid(rand(), true));
            $hyphen = chr(45);
            $uuid =  substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid, 12, 4).$hyphen
                    .substr($charid, 16, 4).$hyphen
                    .substr($charid, 20, 12);
            if ($includeBrackets) {
                $uuid = chr(123) . $uuid . chr(125);
            }
            return $uuid;
        }
    }

    /**
     * Convert a 32 character uuid (md5 hash) to a 36 character guid.
     * Just adds the proper dashes, does not add brackets.
     * @param string $uuid
     * @return string GUID
     */
    public static function uuidToGuid($uuid)
    {
        if (strlen($uuid) == 32) {
            return
                substr($uuid, 0, 8) . '-' .
                substr($uuid, 8, 4) . '-' .
                substr($uuid, 12, 4) . '-' .
                substr($uuid, 16, 4) . '-' .
                substr($uuid, 20);
        }
    }

    /**
     * Convert a mssql binary guid to a string guid
     * @param  binary string $binary
     * @return string
     */
    public static function binaryToGuid($binary)
    {
        $unpacked = unpack('Va/v2b/n2c/Nd', $binary);
        return sprintf('%08X-%04X-%04X-%04X-%04X%08X', $unpacked['a'], $unpacked['b1'], $unpacked['b2'], $unpacked['c1'], $unpacked['c2'], $unpacked['d']);
        // Alternative: http://www.scriptscoop.net/t/c9bb02ec9fdb/decoding-base64-guid-in-python.html
        // Alternative: http://php.net/manual/en/function.ldap-get-values-len.php
    }

    /**
     * Generate a 32 character md5 hash from a string.
     * If string = null generates from a random string.
     * @param string $string optional run md5 on this string instead of random
     * @return 32 character md5 hash string
     */
    public static function getMd5($string=null)
    {
        if (!$string) {
            return md5(uniqid(rand(), true));
        } else {
            return md5($string);
        }
    }

    /**
     * Slugify a string.
     * @param  string $string
     * @return string slugified
     */
    public static function slugify($string)
    {
        $string = trim(strtolower($string));
        $string = preg_replace('/ |_|\/|\\\/i', '-', $string); # space_/\ to -
        $string = preg_replace('/[^\w-]+/i', '', $string); # non alpha-numeric
        $string = preg_replace('/-+/i', '-', $string);     # multiple -
        $string = preg_replace('/-$/i', '-', $string);     # trailing -
        $string = preg_replace('/^-/i', '-', $string);     # leading -
        return $string;
    }

    /* Removes all non ascii characters (32-126) and converts some special msword like characters to their equivalent ascii
     * @param string $data
     * @param boolean $trim = true trim string
     * @param boolean $blankToNull = false converts a blank string into null
     * @return string
     */
    public static function toAscii($data, $trim = true, $blankToNull = false)
    {
        if (isset($data)) {
            // Sample.  This will convert MSWORD style chars + all sorts of UTF-8 chars into proper ASCII...very nice!
            #dirty  : MSWord – ‘ ’ “ ” • … ‰ á|â|à|å|ä ð|é|ê|è|ë í|î|ì|ï ó|ô|ò|ø|õ|ö ú|û|ù|ü æ ç ß abc ABC 123 áêìõç This is the Euro symbol '€'. Žluťoučký kůň\n and such
            #toAscii: MSWord - ' ' " " o ... ? a|a|a|a|a ?|e|e|e|e i|i|i|i o|o|o|oe|o|o u|u|u|u ae c ss abc ABC 123 aeioc This is the Euro symbol 'EUR'. Zlutoucky kun and such

            // Detect encoding.  If bad, will usually be UTF-8
            $encoding = mb_detect_encoding($data);
            if ($encoding != "ASCII") {

                // DO NOT run through utf8_encode() first as that will mess up iconv
                // If your string needs it, do it before you send to this function
                $msWordChars = ['–'=>'-', '—'=>'-', '‘'=>"'", '’'=>"'", '“'=>'"', '”'=>'"', '„'=>'"', '…'=>'...', '•'=>'o', '‰'=>'%'];
                $data = strtr($data, $msWordChars);

                try {
                    // Notice NOT IGNORE, will only fail if needs converted to utf8_encode
                    $data = iconv($encoding, 'ASCII//TRANSLIT', $data);
                } catch (\Exception $ex) {
                    // If failed, there were thinks like b"Orléans" which need utf8_encode FIRST before iconv
                    $data = utf8_encode($data);
                    $data = iconv($encoding, 'ASCII//TRANSLIT//IGNORE', $data); // Notice IGNORE this time
                }
            }

            // Remove all other non-ascii characters (shouldn't be any after iconv, but just in case)
            // Not sure I need this ?
            #$data = preg_replace('/[^[:print:]]/', '', $data); #Shows only ascii 21-126 (plain text)

            if ($trim) {
                $data = trim($data);
            }
            if ($blankToNull && $data == "") {
                $data = null;
            }
        }
        return $data;
    }

    /**
     * Unserialize data only if serialized
     * @param  data $value
     * @return mixed
     */
    public static function unserialize($value)
    {
        if (static::isSerialized($value)) {
            $data = @unserialize($value);
            if ($value === 'b:0;' || $data !== false) {
                // Unserialization passed, return unserialized data
                return $data;
            } else {
                // Data was not serialized, return raw data
                return $value;
            }
        } else {
            return $value;
        }
    }

    /**
     * Check if string is serialized
     * @param  mixed $data
     * @return boolean
     */
    public static function isSerialized($data)
    {
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }

        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }
        return false;
    }
}
