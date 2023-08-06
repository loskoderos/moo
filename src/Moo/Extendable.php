<?php

namespace Moo;

abstract class Extendable
{
    public function __call($name, $args)
    {
        foreach ($this as $key => $callback) {
            if ($key == $name) {
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $args);
                } else {
                    throw new \BadMethodCallException(get_class($this) . "::$name is not callable");
                }      
            }
        }
        throw new \BadMethodCallException(get_class($this) . "::$name is not callable");
    }
}
