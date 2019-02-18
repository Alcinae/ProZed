<?php
function pageLogic($previousData){
    $ret = [];
    
    global $root_domain, $prefix;
    
    $db = getDB();
    
    $canRegister = new DateTime($previousData["currentChallenge"]["end"]) > new DateTime("now");
    
    if(isset($_POST["csrf"]) && isset($_POST["roleSelector"]) && $canRegister){
        if(csrf($_POST["csrf"])){
            $genToken = genUUID();
            $queryRlt = $db->prepare("INSERT INTO register_token(uuid, role, date) VALUES(:uuid, :role, NOW());");
            $queryRlt->bindValue(":uuid", $genToken);
            $queryRlt->bindValue(":role", $_POST["roleSelector"]);
            
            if($queryRlt->execute()){
                $ret["generatedToken"] = "$root_domain$prefix/register.html?token=$genToken";
            }else{
            
            }

        }else{
            //error
        }
    }elseif(isset($_GET["del"]) && csrf($_GET["token"])){
        $queryRlt = $db->prepare("DELETE FROM members WHERE id = :id;");
        //remember. nobody can access this page if not already admin, because the check is done inside index.php
        $queryRlt->bindValue(":id", $_GET["del"]);
        if($queryRlt->execute()){
        
        }else{
        
        }
    }
    
    
    $queryObj = $db->query("SELECT id, lname, city, family_size, email, role FROM members ORDER BY register_date DESC");
    
    $ret["memberList"] = $queryObj->fetchAll();
    
    $ret["canRegister"] = $canRegister;
    
    return $ret;
}

?>
