<?php

namespace Moo;

class Template extends Extendable
{
    protected string $lookupDir;
    protected array $defaultContext;

    public function __construct(string $lookupDir, array $defaultContext = [])
    {
        $this->lookupDir = $lookupDir;
        $this->defaultContext = $defaultContext;
    }

    public function render(string $script, array $context = null): string
    {
        $__path = $this->lookupDir . '/' . $script;
        if (!file_exists($__path)) {
            throw new \RuntimeException("Template not found: " . $script);
        }
        try {
            ob_start();
            extract($this->defaultContext);
            extract(is_array($context) ? $context : []);
            if (false === @include $__path) {
                throw new \RuntimeException("Broken template: " . $script);
            }
                
        } catch (\Exception $exc) {
            ob_end_clean();
            throw $exc;
        }
        return ob_get_clean();
    }
}
