<?php

namespace Moo;

final class OutputBuffer
{
    private function __construct() {}

    public static function begin()
    {
        ob_start();
    }

    public static function end()
    {
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}
