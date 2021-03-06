<?php 
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'Twig';
    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/Twig/';
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        //require($class . '.php');
        return;
    }
    // get the relative class name
    $relative_class = substr($class, $len);
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir.str_replace('_', '/', $relative_class) . '.php';
    // if the file exists, require it
    if (file_exists($file)) {
        require($file);
    }
});

spl_autoload_register(function ($class) {
    $dir = __DIR__ . "/Color/";
    $file = "Color.php";
    if($class === "Color")
        if(file_exists($dir.$file)) 
            require($dir.$file);
});

spl_autoload_register(function ($class) {
     $prefix = 'GO\\';

    $base_dir = __DIR__."/php-cron-scheduler/src/GO/";
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir.str_replace('\\', '/', $relative_class). '.php';
    if (file_exists($file)) {
        require($file);
    }
});

spl_autoload_register(function ($class) {
    $prefix = 'PHPMailer\\PHPMailer\\';

    $base_dir = __DIR__."/PHPMailer/src/";
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir.str_replace('\\', '/', $relative_class). '.php';
    if (file_exists($file)) {
        require($file);
    }
});

spl_autoload_register(function ($class) {
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';

    $base_dir = __DIR__."/PhpSpreadsheet/src/PhpSpreadsheet/";
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir.str_replace('\\', '/', $relative_class). '.php';
    if (file_exists($file)) {
        require($file);
    }
});

spl_autoload_register(function ($class) {
    $prefix = 'Psr\\SimpleCache\\';

    $base_dir = __DIR__."/simple-cache/src/";
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir.str_replace('\\', '/', $relative_class). '.php';
    if (file_exists($file)) {
        require($file);
    }
});

?>
