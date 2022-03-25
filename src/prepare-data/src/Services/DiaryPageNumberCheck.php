<?php

namespace App\Services;

class DiaryPageNumberCheck
{
    // Extracts a page number from a blick like this: [1234]
    // (Example will return 1234)
    public static function getPageNumberFromString($input) {
        // Error: if the string does not begin with a opening bracket [
        if(strpos($input, '[') != 0) {
            return false;
        }

        // Error: if the closing bracket ] is not the last symbol in the string.
        if((strrpos($input, ']') + 1) != strlen($input)) {
            return false;
        }

        // Extract the number.
        $count = preg_match('/[0-9]+/', $input, $output_array);

        // Should only contain one number.
        if($count != 1) {
            return false;
        }

        // Convert string to int, return int.
        return intval($output_array[0]);
    }
}