<?php

function constructMenu($menuEntries) //NOTE: we can delete anything we want becasue each time the data only last one page
{
    /*
    $menu = $menuEntries; //NOTE: php copies array, references are for objects
    
    forEach($menu as $key => $entry){ //NOTE: I'm guessing key will contain the numeric index
        if($entry->hasSubmenu())
        {
            $subMenu = [];
            forEach($entry->subEntries() as $subKey => $subEntry){ //TODO: won't work.. because subEntries are inside an object which is a reference
                if(!$_SESSION["user"]->hasCap($subEntry->getClearance()))
                   $entry->removeEntry($subKey);
            }
            
        }else{
            if(!$_SESSION["user"]->hasCap($entry->getClearance()))
                unset($menu[$key]);
        }

    }
    */
    /*
    $menu = [];
    
    forEach($menuEntries as $elm){
        array_push($el)
        if($entry->hasSubmenu())
        {
            array_merge($menu, array($elm))
        }else{
            array_push($elm->)
        }
    } */
    /*
    $return = []; 
   foreach($menuEntries as $k => $v) {
      if(is_array($v)) {
         $tmp = constructMenu($v);
         if(!is_null($tmp))
            $return = array_merge($return, [$k => $tmp]); //recursion
         continue;
      }
      if(!$v->shouldBeShown()) continue;
      array_push($return, $v);
   }
   $hasAtLeastOneEntry = false;
   foreach($return as $k => $v) {
        if(!is_null($v) && !is_array($v) && !$v->subEntries())
            $hasAtLeastOneEntry = true;
   }
   if($hasAtLeastOneEntry)
    return $return;
   else
    return null;*/
    
    $currMenu = [];
    $isEmpty = true;
    foreach($menuEntries as $k => $ent){
        if(is_array($ent))
        {
            $tmp = constructMenu($ent);
            if(!is_null($tmp))
                $currMenu = array_merge($currMenu, array($k => $tmp));
        }else{
            if($ent->shouldBeShown()){
                array_push($currMenu, $ent);
                if(!$ent->isTitle())
                    $isEmpty = false;
            }
                
            
        }
    }
    if($isEmpty) return null;
    else return $currMenu;
}
/*
function arrkeys($m, $e, $ret = []){
    foreach($m as $k => $v){
        if(!is_object($v))
        {
            $ret = arrkeys($v, $e, $ret);
        }elseif($v === $e){
            $ret[] = $k;
           
        }
        echo "+";
    }
    return $ret;
}*/

function constructBreadcrumbs($menu, $page, $rootpage){
/*
    $breadcrumbs = [];
    
    if($rootpage != null){
        $breadcrumbs[0] = [$rootpage->getName() => $rootpage->getUrl()];
    }
    
    $path = array_keys($menu, $page);
    $step = 0;
    $max = count($path);
    var_dump($path);
    foreach($menu as $k => $v){
        if($step >= $max) break;
        if(is_array($menu[$path[$step]]))
        {
            $breadcrumbs = array_merge($breadcrumbs, constructBreadcrumbs($menu[$path[$step]], $page));
        }else{
            array_push($breadcrumbs, [$v->getName() => $v->getUrl()]);
        }
    }
    
    
    var_dump(arrkeys($menu, $page));
    return $breadcrumbs;
    */
}

class Page {

    private $url = "";  
    private $clearanceNeeded = ""; 
    private $title = "";
    private $scripts = [];
    
    //unused for now
    private $type = "";
    
    //related/needed classes
    private $controller = "";
    private $view = "";
    
    private $breadcrumbs = [];
    
    public function __construct($urlP, $titleP, $controllerP, $viewP, $clearanceP = "", $scriptsP = [], $path = []) {
        $this->url = $urlP;
        $this->title = $titleP;
        $this->controller = $controllerP;
        $this->clearanceNeeded = $clearanceP;
        $this->scripts = $scriptsP;
        $this->view = $viewP;
        $this->breadcrumbs = $path;
    }
    
    public function getUrl(){
        return $this->url;
    }
    
    public function getBreadcrumbs(){
        return $this->breadcrumbs;
    }
    
    public function getName(){
        return $this->title;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    
    public function getView(){
        return $this->view;
    }
    
    public function getClearance(){
        return $this->clearanceNeeded;
    }
    
    public function getController(){
        return $this->controller;
    }
    
    public function getPageTitle(){
        return $this->title;
    }
    
    public function getPageType(){
        return $this->type;
    }
    
    public function pageInfo()
    {
        $ret = [
        "title" => $this->title,
        "url" => $this->url,
        "scripts" => $this->scripts
        ];
        
        return $ret;
    }
    
    
    

}

class menuEntry {
    private $name;
    private $faIcon;
    private $page;
    private $subEntries = false;
    
    public function __construct($nameP, $iconName, $pageObj = null) {
        $this->name = $nameP;
        $this->faIcon = $iconName;
        $this->page = $pageObj;
        if($pageObj == null)
        {
            $this->subEntries = true;
        }
        
    }
    
    public function shouldBeShown(){

        if($this->isTitle())
           return true;
        else
           return $_SESSION["user"]->hasCap($this->page->getClearance());
    }
    
    public function getName(){
        return $this->name;
    }
    public function getIcon(){
        return $this->faIcon;
    }    
    
    public function getPage(){
        return $this->page;
    }
    
    public function getUrl(){
        return $this->page->getUrl().".html";
    }
    
    public function getClearance(){
        if($this->page)
            return $this->page->getClearance();
        else
            return null;
    }
    
    public function isActive()
    {
        return $GLOBALS['APP_page'] === $this->page;
    }
    
    public function isTitle(){
        return $this->subEntries;
    }
    
}


/*
class menuEntry {
    private $name;
    private $faIcon;
    private $page;
    private $subEntries = [];
    private $clearance = "";
    
    public function __construct($nameP, $iconName, $pageObj = null, $subMenu = []) {
        $this->name = $nameP;
        $this->faIcon = $iconName;
        $this->page = $pageObj;
        $this->subEntries = $subMenu;
        if($pageObj)
        {
        
        }else{
        
        }
    }
    
    public function shouldBeShown(){
        if($_SESSION["user"]->isInitialized())
        {
           return $_SESSION["user"]->hasCap($this->page->getClearance());
        }
    }
    
    public function hasSubmenu(){
        return empty($this->subEntries);
    }
    public function getName(){
        return $this->name;
    }
    public function getIcon(){
        return $this->faIcon;
    }    
    
    public function getPage(){
        return $this->page;
    }
    
    public function removeEntry($i){
        unset($subEntries[$i]);
    }
    
    public function getClearance(){
        if($this->page)
            return $this->page->getClearance();
        else
            return null;
    }
    public function subEntries(){
        return $this->subEntries;
    }
    
}
*/
