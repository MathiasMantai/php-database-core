<?php


if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}


use DbCore\DbCore;

$db = new DbCore("rb_database","localhost", "root", "");

$test = $db->select("SELECT * FROM rb_entries", "fetchAll", []);

var_dump($test);