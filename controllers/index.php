 <?php
if(!$_SESSION["user"]->isInitialized())
    header("Location: login.html");

function pageLogic(){
    $ret = [];
    
    
    
    //value table
    $db = getDB();
    
    $chllgQuery = $db->query("SELECT start, end, months_labels FROM challenges_info ORDER BY end DESC LIMIT 1;");
    $ret["currentChallenge"] = $chllgQuery->fetch();
    
    return $ret;
}
?>
