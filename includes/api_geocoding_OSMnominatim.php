<?php
require_once("conf/config.php");
require_once("includes/commons.php");

function geocode($address){

    // url encode the address
    $address = urlencode($address);

    $url = "https://nominatim.openstreetmap.org/?format=json&addressdetails=1&q={$address}&format=json&limit=1";

    // get the json response
    $resp_json = url_get_contents($url, $GLOBALS["root_domain"].$GLOBALS["prefix"]);
    // decode the json
    $resp = json_decode($resp_json, true);

    return array($resp[0]['lat'], $resp[0]['lon']);

}

?>
