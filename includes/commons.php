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
    If called with an arg return of the given token match, or else return the current token.
*/
function csrf(/* ... */)
{
    if(func_num_args() > 0)
        return $_SESSION["csrf_token"] == func_get_arg(0);
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
    
?>
