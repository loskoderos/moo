<?php

require dirname(__FILE__) . '/../../vendor/autoload.php';

$moo = new Moo\Moo();

$moo->get('/', function () {
  echo "Hello world!";
});

$moo();
