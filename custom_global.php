<?php

global $db, $pageData;

$challengeQuery = getDB()->query("SELECT start, end, months_labels FROM challenges_info ORDER BY end DESC LIMIT 1;");
$data = $challengeQuery->fetch();
$pageData["currentChallenge"] = $data;
$pageData["currentChallenge"]["isRunning"] = new DateTime($data["start"]) < new DateTime("now") && new DateTime($data["end"]) > new DateTime("now");

?>
