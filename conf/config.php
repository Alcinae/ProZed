<?php
$prefix = "/PI_test";



function getDB()
{

    $APP_CONF_DB_NAME = "local_dev_PI";
    $APP_CONF_DB_SERVER = "localhost";
    $APP_CONF_DB_USER = "luluzed";
    $APP_CONF_DB_PASSWORD = "456852";

    $con = new PDO("mysql:host=$APP_CONF_DB_SERVER;dbname=$APP_CONF_DB_NAME", $APP_CONF_DB_USER, $APP_CONF_DB_PASSWORD);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $con;
}

?>
