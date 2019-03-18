<?php
require_once("includes/sys_pages.php");

$pages = [
    //"URL" => new Page("URL", "TITRE", "CONTROLLER", "VIEW", "CLEARANCE", ["script1", ....]);
    "index" => new Page("index", "Luluzed - Tableau de bord", "index", "index", "member", ["graph_simple", "carte"], ["Tableau de bord" => null]),
    
    "404" => new Page("404", "Luluzed - Page introuvable", "", "404", "", []),
    
    "login" => new Page("login", "Luluzed - Connexion", "login", "login", "", []),
    
    "defi_stats" => new Page("defi_stats", "Luluzed - Statistiques", "defi_stats", "defi_stats", "member", ["defi_stats"], ["Tableau de bord" => "index", "Statistiques" => null]),
    
    "membre_edit" => new Page("membre_edit", "Luluzed - Info membre", "membre_edit", "membre_edit", "member", [], ["Tableau de bord" => "index", "Espace membres" => null]),
    
    "defi" => new Page("defi", "Luluzed - Mon defi", "defi", "defi", "Participant", ["defi", "modalConfirmClassic"], ["Tableau de bord" => "index", "Mon Defi" => null]),
    
    "ateliers" => new Page("ateliers", "Luluzed - Agenda", "ateliers", "ateliers", "member", ["ateliers"], ["Tableau de bord" => "index", "Agenda" => null]),
    
    "register" => new Page("register", "Luluzed - Inscription", "register", "register", "", []),
    
    "logout" => new Page("logout", "Luluzed - Deconnexion", "logout", "logout", "", []),
    
    "forgot-password" => new Page("forgot-password", "Luluzed - Mot de passe oublié", "forgot-password", "forgot-password", "", []),
    
    "admin_membres" => new Page("admin_membres", "Luluzed - Administration - Membres", "admin_membres", "admin_membres", "admin", ["admin_membres", "modalConfirmClassic"], ["Tableau de bord" => "index", "Administration" => null, "Membres" => null]),
    
    "admin_actu" => new Page("admin_actu", "Luluzed - Administration - Actualites", "admin_actu", "admin_actu", "admin", ["admin_actu", "modalConfirmClassic"], ["Tableau de bord" => "index", "Administration" => null, "Actualites" => null]),
    
    "admin_ateliers" => new Page("admin_ateliers", "Luluzed - Administration - Ateliers", "admin_ateliers", "admin_ateliers", "admin", ["admin_ateliers", "modalConfirmClassic"], ["Tableau de bord" => "index", "Administration" => null, "Agenda" => null]),
    
    "503" => new Page("503", "Luluzed - Acces refusé", "", "503", "", [])
    ];
/*
    $menu = [
        new menuEntry("Tableau de Bord", "fa-tachometer-alt", $pages["index"]),
        new menuEntry("Statistiques", "fa-chart-area", $pages["defi_stats"]),
        new menuEntry("Mon défi", "fa-table", $pages["defi"]),
        new menuEntry("Agenda", "fa-calendar-alt", $pages["ateliers"]),
        new menuEntry("Administration", "fa-tachometer-alt", null, [ //TODO: ajouter la possibilite d'ajouter un titre dans le sous menu
            new menuEntry("Membres", "fa-tachometer-alt", $pages["index"]),
            new menuEntry("Tableau de Bord", "fa-tachometer-alt", $pages["index"]),
            new menuEntry("Tableau de Bord", "fa-tachometer-alt", $pages["index"])
        ]),
    ];
    */
    
    //NOTE: unfortunately twig does not seem to handle mixed keys ? when the keys are removed for the object it, I can't get the current loop value.
    //NOTE: This means that the keys are useless(eg you can leave them blank ) unless there is a submenu, in this case it MUST be unique.
    $menu = [
        "1" => new menuEntry("Tableau de Bord", "fa-tachometer-alt", $pages["index"]),
        "2" => new menuEntry("Statistiques", "fa-chart-area", $pages["defi_stats"]),
        "3" => new menuEntry("Mon défi", "fa-table", $pages["defi"]),
        "4" => new menuEntry("Agenda", "fa-calendar-alt", $pages["ateliers"]),
        "subMenu" => [
            new menuEntry("Administration", "fa-folder"), //the first one is always the submenu entry
            
            new menuEntry("Administration", ""), //this one is not the first, so it will be used as list header
            new menuEntry("Membres", "fa-tachometer-alt", $pages["admin_membres"]),
            new menuEntry("Actualitees", "fa-tachometer-alt", $pages["admin_actu"]),
            new menuEntry("Agenda", "fa-tachometer-alt", $pages["admin_ateliers"])
        ]
    ];


?>
