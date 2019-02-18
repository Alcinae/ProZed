 <?php
require_once("conf/config.php");

$db = getDB();
$userCount = $db->query("SELECT count( DISTINCT(member) ) as count FROM challenge;");

$db->prepare("UPDATE challenge_info SET members = ? ORDER BY end DESC LIMIT 1;");
$db->execute([$userCount["count"]]);

$db->query("INSERT INTO challenges_info(start, end) VALUES(DATE_ADD(DATE_ADD(MAKEDATE(YEAR(NOW()), 1), INTERVAL (11)-1 MONTH), INTERVAL (1)-1 DAY), DATE_ADD(DATE_ADD(MAKEDATE(YEAR(NOW())+1, 1), INTERVAL (6)-1 MONTH), INTERVAL -1 DAY))");
//Okay so the -1 day interval allow us to have the last day of the previous month. I could hardcode the 31st, yes i could have done that, but I didn't.
//MAKEDATE with the year then 1 return the first day of the first month of the year given. 
//We can then combine mutliple date to build the one we need.

//TODO: call exportdata.php to archive challenge data


$db->query("DELETE FROM members WHERE admin != 1;"); //cleanup users.

$db->query("DELETE FROM challenge;"); //cleanup previous challenge data.

?> 
