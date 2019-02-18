<?php

require_once("vendor/php/autoload.php");

use GO\Scheduler;

$scheduler = new Scheduler([
    'tempDir' => __DIR__."/runTimeData"
]); 

$scheduler->php("crons/challenge_restart.php")->september(1)->onlyOne();
$scheduler->php("crons/events_messages_cleanup.php")->hourly()->onlyOne();
$scheduler->php("crons/events_mail_notice.php")->daily()->onlyOne();
$scheduler->php("crons/register_token_cleanup.php")->monthly()->onlyOne();
$scheduler->php("crons/challenge_endMonth.php")->monthly()->onlyOne();

//TODO:remove this
echo $scheduler->getVerboseOutput("html");

$scheduler->run();
?>
