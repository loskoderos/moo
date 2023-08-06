<?php

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Moo\Moo;
use Moo\Template;

$moo = new Moo();

// Create template renderer.
$moo->template = function (string $script, mixed $context = null) use ($moo): string  {
    // Template and default context.
    $template = new Template(__DIR__ . '/templates', [
        'baseUri' => $moo->request->baseUri
    ]);

    // Add 'date' plugin.
    $template->date = function () {
        return date('Y-m-d');
    };

    // Render template.
    return $template->render($script, $context);
};

// Index page handler.
$moo->get('/', function () use ($moo) {
    return $moo->template('index.phtml', [
        'hello' => 'Hello World!'
    ]);
});

// Features page handler.
$moo->get('/features', function () use ($moo) {
    return $moo->template('features.phtml', [
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
    return $moo->template('contact.phtml', [
        'features' => []
    ]);
});

// Contact form submit handler.
$moo->post('/contact', function () use ($moo) {
    return strrev($moo->request->post->message);
});

$moo();
