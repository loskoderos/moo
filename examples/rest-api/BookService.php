<?php

class BookService
{
    public function getBooks()
    {
        return [1, 2, 3];
    }

    public function addBook($dto)
    {

    }

    public function getBook($id)
    {
        return ['title' => 'Don Quixote', 'author' => 'Miguel de Cervantes'];
    }

    public function updateBook($id, $dto)
    {

    }

    public function removeBook($id)
    {
        
    }
}
