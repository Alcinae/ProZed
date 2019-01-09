<?php

require_once("vendor/php/autoload.php");
require_once("includes/commons.php");
require_once("conf/config.php");
require_once("conf/pages_config.php");
require_once("includes/sys_pages.php");
require_once("includes/sys_account.php");
session_start();
genCSRF();


global $pages, $menu; //from the pages_config file

if(!isset($_SESSION["user"]))
    $_SESSION["user"] = new User();

if(isset($_GET["page"])){
    $tmp_page = s($_GET["page"]);
}else{
    $tmp_page = "index";
}

/*
if(isset($_GET["page"])){
    $_GET["page"] = "index";
}
*/

if(!array_key_exists($tmp_page, $pages)){
    $tmp_page = "404";
    header('HTTP/1.0 404 Not Found', true, 404);
}

if(!$_SESSION["user"]->hasCap($pages[$tmp_page]->getClearance()))
{
    $tmp_page = "503";
    header('HTTP/1.0 503 Forbidden', true, 503);
}
$APP_page = $pages[$tmp_page];
$GLOBALS['APP_page']= $APP_page;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$loader = new Twig_Loader_Filesystem(__DIR__.'/views/');
$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__.'/cache/views/',
));

if(file_exists("controllers/{$APP_page->getController()}.php"))
    include("controllers/{$APP_page->getController()}.php");

$pageData = [];
if(function_exists("pageLogic")){
    $tmp = pageLogic();
    if(is_array($tmp))
        $pageData = array_merge($tmp, $pageData);
}else{
    
}
$pageData['pageData'] = $APP_page->pageInfo();
$pageData['csrf'] = csrf();
$pageData["menu"] = constructMenu($menu);
$pageData["breadcrumbs"] = $APP_page->getBreadcrumbs();

//INFOBOXES
$query = getDB()->query("SELECT message, style FROM news WHERE expire >= NOW()");
$pageData['infos'] = $query->fetchAll();

/*
echo "<!--";
var_dump($pageData["menu"]);
var_dump($_SESSION["user"]->hasCap("admin"));
echo "-->";
*/

echo $twig->render("{$APP_page->getView()}.html", $pageData);
/*
echo "test";
if(isset($_GET["page"]))
    echo $_GET["page"]."<br>";
if(isset($_GET["data"]))
    echo $_GET["data"];
*/


?>
