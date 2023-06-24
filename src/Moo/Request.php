<?php

namespace Moo;

use LosKoderos\Generic\Collection\Collection;
use LosKoderos\Generic\Model\Model;

class Request extends Model
{
    public string $method;
    public string $uri;
    public Collection $headers;
    public Collection $query;
    public Collection $post;
    public Collection $files;
    public $body;
    
    public function __construct(mixed $mixed = null)
    {
        $this->method = 'GET';
        $this->uri = '/';
        $this->headers = new Collection();
        $this->query = new Collection();
        $this->post = new Collection();
        $this->files = new Collection();
        parent::__construct($mixed);
    }
}
