<?php

function sanitize_input($input){
    return htmlspecialchars(stripslashes(trim($input)));
}

function s($input){
    return sanitize_input($input);
}

function gen_error($t, $msg)
{
    return [$t => $msg];
}

function genUUID()
{
    return base64_encode(uniqid().random_bytes(32));
}

/**
    If called with an arg return if the given token match, or else return the current token.
*/
function csrf(/* ... */)
{
    if(func_num_args() > 0)
        return $_SESSION["csrf_token"] === func_get_arg(0);
    else
        return $_SESSION["csrf_token"];
}

function genCSRF(){
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
}

/**
* Searches haystack for needle and 
* returns an array of the key path if 
* it is found in the (multidimensional) 
* array, FALSE otherwise.
*
* @mixed array_searchRecursive ( mixed needle, 
* array haystack [, bool strict[, array path]] )
*/
 
function array_searchRecursive( $needle, $haystack, $strict=false, $path=array() )
{
    if( !is_array($haystack) ) {
        return false;
    }
 
    foreach( $haystack as $key => $val ) {
        if( is_array($val) && $subPath = array_searchRecursive($needle, $val, $strict, $path) ) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) ) {
            $path[] = $key;
            return $path;
        }
    }
    return false;
}

function array_clone($array) {
    return array_map(function($element) {
        return ((is_array($element))
            ? array_clone($element)
            : ((is_object($element))
                ? clone $element
                : $element
            )
        );
    }, $array);
}

if ( ! function_exists( 'array_key_last' ) ) {
    /**
     * Polyfill for array_key_last() function added in PHP 7.3.
     *
     * Get the last key of the given array without affecting
     * the internal array pointer.
     *
     * @param array $array An array
     *
     * @return mixed The last key of array if the array is not empty; NULL otherwise.
     */
    function array_key_last( $array ) {
        $key = NULL;

        if ( is_array( $array ) ) {

            end( $array );
            $key = key( $array );
        }

        return $key;
    }
}

function isValidFullTime($time) {
    return isValidDateTime($time, "H:i:s");
}

function isValidDateTime($datetime, $format='Y-m-d H:i:s') {
    $d = DateTime::createFromFormat("$format", "$datetime");
    return $d && $d->format($format) == $datetime;
}

function isValidISODate($date) {
    return isValidDateTime($date, "Y-m-d");
}

function isValidTime($time) {
    return isValidDateTime($time, "H:i");
}
    
?>
