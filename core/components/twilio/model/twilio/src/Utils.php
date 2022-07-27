<?php

namespace MODX\Twilio;

class Utils
{

    public static function explodeAndClean($array, $delimiter = ',')
    {
        $array = explode($delimiter, $array);            // Explode fields to array
        $array = array_map('trim', $array);       // Trim array's values
        $array = array_keys(array_flip($array));  // Remove duplicate fields
        $array = array_filter($array);            // Remove empty values from array

        return $array;
    }
}
