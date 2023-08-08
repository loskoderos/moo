<?php

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Moo\Moo;
use Moo\Template;

$moo = new Moo();

// Create template renderer.
$moo->template = new Template(__DIR__ . '/templates', [
    'title' => 'Moo Sample Website'
]);

// Create plugin to extract baseUri.
$moo->template->baseUri = function () use ($moo) {
    return $moo->request->baseUri;
};

// Create example date plugin.
$moo->template->date = function () {
    return date('Y-m-d');
};

// Index page handler.
$moo->get('/', function () use ($moo) {
    return $moo->template->render('index.phtml', [
        'hello' => 'Hello World!'
    ]);
});

// Features page handler.
$moo->get('/features', function () use ($moo) {
    return $moo->template->render('features.phtml', [
        'features' => [
            'Simple regex routing',
            'Extendable Moo class',
            'Lightweight and fast',
            'Allows nesting',
            'Builtin PHP templating'
        ]
    ]);
});

// Contact page handler.
$moo->get('/contact', function () use ($moo) {
    return $moo->template->render('contact.phtml', [
        'features' => []
    ]);
});

// Contact form submit handler.
$moo->post('/contact', function () use ($moo) {
    return strrev($moo->request->post->message);
});

$moo();
