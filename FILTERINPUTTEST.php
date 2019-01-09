<?php
$args = [
"email" => FILTER_VALIDATE_EMAIL,
"int" => FILTER_VALIDATE_INT,
"bool" => FILTER_VALIDATE_BOOLEAN,
"string" => FILTER_SANITIZE_STRING,
 "fname" => array("filter" => FILTER_VALIDATE_REGEXP,
            "options" => array("regexp" => "/^[\p{L}'][ \p{L}'-]*[\p{L}]$/u"))
];

$inputs = filter_input_array(INPUT_GET, $args);

echo "Data:";
var_dump($inputs);

echo base64_encode(uniqid().random_bytes(32));
?>
