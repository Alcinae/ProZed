<?php
require_once("includes/api_geocoding_OSMnominatim.php");

class User {
    private $id = "-1";
    private $fname = "Anonymous";
    private $lname = "";
    private $email = "";
    private $caps = ["" => true];
    private $prefs = [];
    private $initialized = false;
    private $role = "";
    
    //public function __construct(){
    //echo "4.";
    //}
    
    
    public function getID(){
        return $this->id;
    }
    public function getFName(){
        return $this->fname;
    }
    
    public function getRole(){
        return $this->role;
    }
    public function getLName(){
        return $this->lname;
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    public function hasCap($cap){ //WARNING: this is pretty ugly so be careful and check what loadData does too.

        $ret = array_key_exists($cap,$this->caps); //TODO: i don't know, do something this is atrocious
        if($cap === "") $ret = true;
        return $ret != false && $ret != NULL && $this->caps[$cap] != false;
    }
    
    public function pref($pref){
        if(array_key_exists($pref,$this->prefs));
            return $this->prefs[$pref];
        return false;
        /*
        if($ret)
            return $ret;
        else
            return false;
            */
    }
    
    public function isInitialized(){
        return $this->initialized;
    }
    
    public function login($idstr, $password){
        $db = getDB();
        
        $query = $db->prepare("SELECT id, password FROM members WHERE email=:email");
        $query->bindValue(':email', filter_var($idstr, FILTER_SANITIZE_STRING), PDO::PARAM_STR);
        //$query->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $query->execute();
        $data = $query->fetch();
        if(!password_verify($password, $data["password"]))
            return false;
 
        return $this->loadData($data["id"]);
    }
    
    public function reloadData(){
        if($this->initialized)
            return $this->loadData($this->id);
        else
            return false;
    }
    
    public function loadData($id){
        $db = getDB();
        
        $query = $db->prepare("SELECT * FROM members WHERE id=:id");
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $AllData = $query->fetchAll();
        
        if(count($AllData) != 1)
            return false;
            
        $data = $AllData[0];
        $this->id = $data["id"];
        $this->fname = $data["fname"];
        $this->lname = $data["lname"];
        $this->caps["admin"] = (bool) $data["admin"];
        $this->caps[$data["role"]] = true;
        $this->role = $data["role"];
        $this->caps["member"] = true;
        $this->email = $data["email"];
        
        $this->prefs["consent_email"] = $data["consent_email"];
        $this->prefs["consent_map"] = $data["consent_map"];
        
        $this->initialized = true;
        
        return true;
    }
    
    /**
        This function is used to parse/validate/store POST data from forms. IF the user is already logged in, it updates the data.
    **/
    public function registerFromPOST($role = "", $checkOldPassword = true){
        
        $update = $this->initialized; //If we are already registered, we only want to update the data
        $db = getDB();
        //var_dump($update);

        //all POST fields to validate
        $args = [
            "fname" => FILTER_SANITIZE_STRING,
            "lname" => FILTER_SANITIZE_STRING,
            "email" => FILTER_VALIDATE_EMAIL,
            "city" => FILTER_SANITIZE_STRING,
            "address" => FILTER_SANITIZE_STRING, //FILTER_NULL_ON_FAILURE
            "family_size" => array("filter" => FILTER_VALIDATE_INT, "options" => array("default" => 1, "min_range" => 1)),
            "password" => FILTER_SANITIZE_STRING,
            "password2" => FILTER_SANITIZE_STRING,
            "consent_email" => array("filter" => FILTER_VALIDATE_BOOLEAN, "flags" => FILTER_NULL_ON_FAILURE),
            "consent_map" => array("filter" => FILTER_VALIDATE_BOOLEAN, "flags" => FILTER_NULL_ON_FAILURE)/*,
            "role" => FILTER_DEFAULT */
            ];

        //required fields and their error message
        $required = ["fname" => "Prénom invalide ou vide !","lname" => "Nom invalide ou vide !","email" => "Adresse email invalide.","city" => "Ville invalide.","family_size" => "Nombre de personnes dans la famille invalide.","password" => "Mot de passe invalide."/*,"password2" => "Mot de passe de vérification invalide."*/];
        
        
        if($update && $checkOldPassword){ //add the new fields if the form was an update form
            $args["oldPassword"] = FILTER_SANITIZE_STRING;
            $required["oldPassword"] = "Vous devez entrer votre mot de passe actuel !"; 
        }
        
        $inputs = filter_input_array(INPUT_POST, $args); //magic
        
        
        
        $passwordUpdated = false; //only used if $update == true
        if($update){
            $passwordUpdated = !empty($inputs["password"]);
        }
        
        unset($_POST["password"]);
        unset($_POST["password2"]);
        if($inputs["password"] != $inputs["password2"] && !$passwordUpdated){
            return gen_error("","Erreur: Mot de passe non identiques.");
        }
        
        //var_dump($inputs);
        
        foreach($inputs as $key => $value){
            if(array_key_exists($key, $required)){
                if($inputs[$key] == NULL && ($key != "password" && !$passwordUpdated)) //check if any required entry is empty, also check if password is empty because we did not change it
                    return gen_error("", $required[$key]);
            }
        }
        
        //var_dump($checkOldPassword);
        if($update && $checkOldPassword) 
        { //check if old password is good
        
            $query = $db->prepare("SELECT id, password FROM members WHERE email=:email");
            $query->bindValue(':email', $this->getEmail(), PDO::PARAM_STR);
            //$query->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $query->execute();
            $data = $query->fetch();
            if(!password_verify($inputs["oldPassword"], $data["password"]))
                return gen_error("","Erreur: Mot de passe incorrect.");
        }
        
        
        //for checkboxes...
        /*
        foreach($inputs as $key => $value){
            if($value == NULL)
            {
                $inputs[$key] = false;
            }
        }
        */
        
        
        //var_dump($inputs);
        
        $inputs["password"] = password_hash($inputs["password"], PASSWORD_DEFAULT);
        unset($inputs["password2"]);
            
            //CHECKING if user already exists
        $query = $db->prepare("SELECT id FROM members WHERE email=:email AND id != :id"); 

        $query->bindValue(":email", $inputs["email"]);
        
        
        $query->bindValue(":id", ($update) ? $this->getID() : null);
        
        $query->execute();
        $data = $query->fetchAll();
        //var_dump($data);
        if((/*!$update && */count($data) != 0) /*|| ($update && $data[0]["id"] != $this->id)*/)  //Does the email exist already, if yes, is it the same person and we are just updating it ?
        {
        //}elseif(count($data) != 0)
            return gen_error("", "Cette adresse email est deja utilisee.");
        }
           
        unset($query);
        unset($data);

        
        
        //NOTE: dynamically building the query should be safe, because only the keys of the $args array are used. filter_input_array should copy them to the $inputs array.
            
        //add role
        if(!empty($role))
            $inputs["role"] = $role;
        
        $queryCmd = "";
        
        if($update){
            unset($inputs["oldPassword"]); //we must remove all unused data, because we will be interating over it and binding all data in the array to the DB prepared statement.
            
            if(!$passwordUpdated){
                unset($inputs["password"]);
                unset($inputs["password2"]);
            }
            
            //Building query from the filtered info
            $queryCmd = "UPDATE members SET ";
            foreach($inputs as $key => $value){
                $queryCmd .= "{$key} = :{$key}, ";
            
            }

            $queryCmd .= "WHERE id = :id;"; // will use current stored id (in this class) because it won't change
            $inputs["id"] = $this->id;
            
            $queryCmd = str_replace(", WHERE", " WHERE", $queryCmd); //properly terminate list in the query
            
        }else{
        
            //Building query from the filtered info
            $queryCmd = "INSERT INTO members(";
            foreach($inputs as $key => $value){
                $queryCmd .= "{$key}, ";
            
            }
            //$queryCmd = substr($queryCmd, 0, -2); //useless now that we added a hardcoded las field: register_date. Was sued to remvoe the trailing pace and comma
            $queryCmd .= "register_date, admin) VALUES(";
            
            foreach($inputs as $key => $value){
                $queryCmd .= ":{$key}, ";
            
            }
            //$queryCmd = substr($queryCmd, 0, -2);
            $queryCmd .= "NOW(), false)";
            //str_replace(", )", ")", $queryCmd); //properly terminate list in the query
            
        }
        
        //var_dump($queryCmd);
        $query = $db->prepare($queryCmd);
        foreach($inputs as $key => $value){
            $query->bindValue(":{$key}", $value);
        }
            
        $result = $query->execute();
        
        
        ////////
        
        $id = -1;
        if($update)
            $id = $this->getID();
        else
        {
            $idQuery = $db->prepare("SELECT id FROM members WHERE members.email = :email;");
            $idQuery->bindValue(":email", $inputs["email"]);
                
            $id = $idQuery->fetch()["id"];
        }
        
        if($inputs["consent_map"]){
            $coord = geocode($inputs["address"].", ".$inputs["city"].", France");
            
            $locationQuery = $db->prepare("INSERT INTO locations (latitude, longitude, member) VALUES(:lat, :long, :member) ON DUPLICATE KEY UPDATE latitude=:lat2, longitude=:long2;");
            
            $locationQuery->bindValue(":lat", $coord[0]);
            $locationQuery->bindValue(":long", $coord[1]);
            $locationQuery->bindValue(":member", $id);
            $locationQuery->bindValue(":lat2", $coord[0]);
            $locationQuery->bindValue(":long2", $coord[1]);
            
            $locationQuery->execute();
        
        }else{
            $locationQuery = $db->prepare("DELETE FROM locations WHERE members = ?;");
            $locationQuery->execute([$id]);
        }
        
        
        unset($query);
        //return true;
        return $result;
    }
    
    public function logout(){
        if(!$this->initialized) 
            return false;
        
        $this->initialized = false;
        unset($_SESSION["user"]);
        session_destroy();
    }
    
    public function setAdmin($yn){
        $db = getDB();
        $query = $db->prepare("UPDATE members SET admin = ? WHERE id = ?");
        $query->execute([$yn, $this->id]);//TODO check if successful
        return $this->reloadData();
    }
    




}


?>
