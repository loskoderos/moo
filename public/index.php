<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

require dirname(__FILE__) . '/../vendor/autoload.php';

$moo = new Moo\Moo();

$moo->dumpServerVariables = function () {
  var_dump($_SERVER);
  var_dump($_GET);
  var_dump($_POST);
  var_dump($_FILES);
};

$moo->route('/(.*)', function () use ($moo) {
  $moo->dumpServerVariables();
});

$moo();
