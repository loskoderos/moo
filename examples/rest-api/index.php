<?php

require dirname(__FILE__) . '/../../vendor/autoload.php';
require dirname(__FILE__) . '/BookService.php';

$moo = new Moo\Moo();
$moo->bookService = new BookService();

$moo->before = function () use ($moo) {
    $moo->request->body = file_get_contents('php://input');
};

$moo->after = function () use ($moo) {
    $moo->response->headers->set('Content-Type', 'application/json');
    $moo->response->body = json_encode($moo->response->body);
};

$moo->get('/books', function () use ($moo) {
    return $moo->bookService->getBooks();
});

$moo->post('/books', function () use ($moo) {
    return $moo->bookService->addBook($moo->request->body);
});

$moo->get('/books/(\d+)', function ($id) use ($moo) {
    return $moo->bookService->getBook($id);
});

$moo->put('/books/(\d+)', function ($id) use ($moo) {
    return $moo->bookService->updateBook($id, $moo->request->body);
});

$moo->delete('/books/(\d+)', function ($id) use ($moo) {
    return $moo->bookService->removeBook($id);
});

$moo();
