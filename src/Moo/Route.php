<?php

namespace Moo;

use LosKoderos\Generic\Model\Model;

class Route extends Model {
    public string $method;
    public string $uri;
    public mixed $callback;
}
