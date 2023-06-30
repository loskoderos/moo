<?php 

require dirname(__FILE__) . '/BookService.php';

use Moo\Moo;
use Moo\Response;
use Moo\StatusCode;

class Application extends Moo
{
    protected BookService $bookService;

    public function __construct()
    {
        parent::__construct();

        $this->bookService = new BookService();

        $this->get(     '/',            [$this, 'index']);
        $this->get(     '/books',       [$this, 'getBooks']);
        $this->post(    '/books',       [$this, 'addBook']);
        $this->get(     '/books/(\d+)', [$this, 'getBook']);
        $this->put(     '/books/(\d+)', [$this, 'updateBook']);
        $this->delete(  '/books/(\d+)', [$this, 'removeBook']);
    }

    public function before()
    {
        $this->request->body = file_get_contents('php://input');
    }

    public function after()
    {
        $this->response->headers->set('Content-Type', 'application/json');
        $this->response->body = json_encode($this->response->body);
    }

    public function error(\Exception $exc)
    {
        $this->response = new Response();
        $this->response->code = $exc->getCode() > 0 ? $exc->getCode() : 500;
        $this->response->message = StatusCode::message($this->response->code);
        $this->response->headers->set('Content-Type', 'application/json');
        $this->response->body = json_encode(['error' => $exc->getMessage()]);
    }

    public function index()
    {
        return ['time' => date('Y-m-d H:i:s')];
    }

    public function getBooks()
    {
        return $this->bookService->getBooks();
    }

    public function addBook()
    {
        return $this->bookService->addBook($this->request->body);
    }

    public function getBook($id)
    {
        return $this->bookService->getBook($id);
    }

    public function updateBook($id)
    {
        return $this->bookService->updateBook($id, $this->request->body);
    }

    public function removeBook($id)
    {
        return $this->bookService->removeBook($id);
    }
}
