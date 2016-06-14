<?php
require __DIR__ . '/lib/password.php';
require __DIR__ . '/lib/string.php';

spl_autoload_register(function ($class) {

    $paths = preg_split('|\\\\|i', $class);

    if ($paths[0] != "ztphplibs") {
        echo "Error:[autoload only support ztphplibs. Your Class is {$class}]\n";
    }

    array_splice($paths, 2, 0, array("src"));

    $prefix = dirname(dirname(dirname(dirname(__FILE__))));

    include_once $prefix . "/" . join("/", $paths) . ".php";
});