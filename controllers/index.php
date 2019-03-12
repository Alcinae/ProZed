 <?php
function pageLogic($previousData){
 
    
    if(!$_SESSION["user"]->isInitialized())
        header("Location: login.html");
        
    $tmp = @explode("/", $_SERVER['HTTP_REFERER']);    
    if($_SESSION["user"]->hasCap("Ref") && $tmp[count($tmp)-1] == "login.html")
        header("Location: defi_stats.html");
    
    $ret = [];
    
    
    //this is now done in custom_global.php
    /*
    function pageLogic($previousData){
        $ret = [];
        
        
        
        //value table
        $db = getDB();
        
        $chllgQuery = $db->query("SELECT start, end, months_labels FROM challenges_info ORDER BY end DESC LIMIT 1;");
        $ret["currentChallenge"] = $chllgQuery->fetch();
        
        return $ret;
    }
    */

    $showForm = $_SESSION["user"]->hasCap("Participant");
    if($previousData["currentChallenge"]["isRunning"] && $showForm){
        $db = getDB();
        require_once("controllers/partials/d_form.php");
    }
    $ret["showForm"] = $_SESSION["user"]->hasCap("Participant");
    return $ret;
}
?>
