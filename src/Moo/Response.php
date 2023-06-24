<?php

namespace Moo;

use LosKoderos\Generic\Collection\Collection;
use LosKoderos\Generic\Model\Model;

class Response extends Model
{
    public $body;
    public Collection $headers;
    public int $code;
    public string $message;

    public function __construct(mixed $mixed = null)
    {
        $this->body = null;
        $this->headers = new Collection();
        $this->code = StatusCode::HTTP_OK;
        $this->message = StatusCode::message($this->code);
        parent::__construct($mixed);
    }
}
