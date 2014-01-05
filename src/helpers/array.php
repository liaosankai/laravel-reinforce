<?php

if (!function_exists('in_arrayi')) {

    /**
     * The in_arrayi function is a case-insensitive version of in_array.
     *
     * @param  string  $needle the value to search for
     * @param  array  $haystack the array to search in
     * @return bool
     */
    function in_arrayi($needle, $haystack = array())
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }

}