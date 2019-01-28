<?php
class User {
    private $id = "-1";
    private $fname = "Anonymous";
    private $lname = "";
    private $email = "";
    private $caps = ["" => true];
    private $prefs = [];
    private $initialized = false;
    
    /*
    public function __construct(){
    
    }
    */
    
    public function getID(){
        return $this->id;
    }
    public function getFName(){
        return $this->fname;
    }
    public function getLName(){
        return $this->lname;
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
        if(initialized)
            return loadData($this->id);
        else
            return false;
    }
    
    private function loadData($id){
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
        $this->caps["member"] = true;
        $this->email = $data["email"];
        
        $this->prefs["consent_email"] = $data["consent_email"];
        $this->prefs["consent_map"] = $data["consent_map"];
        
        $this->initialized = true;
        
        return true;
    }
    
    public function registerFromPOST(){
        if($this->initialized) return gen_error("", "Vous etes deja inscrit et connecte.");
        $db = getDB();
        

         /*
        $i_fname = s($_POST["fname"]);
        $i_lname = s($_POST["lname"]);
        $i_email = s($_POST["email"]);
        $i_city = s($_POST["ville"]);
        $i_address = s($_POST["address"]);
        $i_members = s($_POST["members"]);
        $i_password = password_hash($_POST["password"]);
        $i_password2 = password_hash($_POST["password2"]);
        unset($_POST["password"]);
        unset($_POST["password2"]);
        if($i_password != $i_password2)
        {
            return gen_error("","Erreur: Mot de passe non identiques.");
        }
        $i_consent_mail = s($_POST["consent_mail"]);
        $i_fname_consent_map = s($_POST["consent_map"]);*/
        
        $args = [
            "fname" => array("filter" => FILTER_VALIDATE_REGEXP,
                            "options" => array("regexp" => "/^[\p{L}'][ \p{L}'-]*[\p{L}]$/u")),
            "lname" => array("filter" => FILTER_VALIDATE_REGEXP,
                            "options" => array("regexp" => "/^[\p{L}'][ \p{L}'-]*[\p{L}]$/u")),
            "email" => FILTER_VALIDATE_EMAIL,
            "city" => array("filter" => FILTER_VALIDATE_REGEXP,
                            "options" => array("regexp" => "/^[\p{L}'][ \p{L}'-]*[\p{L}]$/u")),
            "address" => array("filter" => FILTER_VALIDATE_REGEXP,
                            "options" => array("regexp" => "/^[\p{L}'][ \p{L}'-]*[\p{L}]$/u")),
            "family_size" => FILTER_VALIDATE_INT,
            "password" => FILTER_SANITIZE_STRING,
            "password2" => FILTER_SANITIZE_STRING,
            "consent_email" => FILTER_VALIDATE_BOOLEAN,
            "consent_map" => FILTER_VALIDATE_BOOLEAN,
            "role" => FILTER_DEFAULT
            ];
            
        $required = ["fname" => "Prénom invalide ou vide !","lname" => "Nom invalide ou vide !","email" => "Adresse email invalide.","city" => "Ville invalide.","family_size" => "Nombre de personnes dans la famille invalide.","password" => "Mot de passe invalide.","password2" => "Mot de passe de vérification invalide."];
        
        $inputs = filter_input_array(INPUT_POST, $args);
        unset($_POST["password"]);
        unset($_POST["password2"]);
        if($inputs["password"] != $inputs["password2"]){
            return gen_error("","Erreur: Mot de passe non identiques.");
        }
        
        //var_dump($inputs);
        
        foreach($inputs as $key => $value){
            if(array_key_exists($key, $required) && $inputs[$key] == NULL){
                return gen_error("", $required[$key]);
            }
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
        $query = $db->prepare("SELECT id FROM members WHERE email=:email");
        $query->bindValue(":email", $inputs["email"]);
        $query->execute();
        $data = $query->fetchAll();
        if(count($data) != 0)
            return gen_error("", "Cette adresse email est deja utilisee.");
        
        unset($query);
        unset($data);
        //NOTE: dynamically building the query should be safe, because only the keys of the $args array are used. filter_input_array should copy them to the $inputs array.
        
        //add role
        $inputs["role"] = $uuid_data[0]["role"];
        
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
        
        
        
        
        $query = $db->prepare($queryCmd);
        foreach($inputs as $key => $value){
            $query->bindValue(":{$key}", $value);
        }
            
        $result = $query->execute();
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
    




}


?>
