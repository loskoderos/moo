<?php

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Moo\Moo;

$booksMoo = new Moo();
$booksMoo->get('/books/(\d+)', function ($id) {
    echo "this is book id=$id";
});

$usersMoo = new Moo();
$usersMoo->get('/users/(\d+)', function ($id) {
    echo "this is user id=$id";
});

$moo = new Moo();
$moo->route('/books/.*', $booksMoo);
$moo->route('/users/.*', $usersMoo);
$moo();
