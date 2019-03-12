<?php
$root_domain = "http://example.com";
$prefix = "/PI_test";

//Its is strongly discouraged to not setup cron. You only need to setup a cron task running /crons.php every minute.
//Setting this to false is unsupported and may cause unpredictable behavior
$isCronEnabled = true;

function getDB()
{

    $APP_CONF_DB_NAME = "local_dev_PI";
    $APP_CONF_DB_SERVER = "localhost";
    $APP_CONF_DB_USER = "luluzed";
    $APP_CONF_DB_PASSWORD = "456852";

    $con = new PDO("mysql:host=$APP_CONF_DB_SERVER;dbname=$APP_CONF_DB_NAME", $APP_CONF_DB_USER, $APP_CONF_DB_PASSWORD);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    return $con;
}

?>
