<?php

namespace XDebugTraceTool;

use Symfony\Component\Console;

class Application extends Console\Application
{
    public function __construct()
    {
        parent::__construct('xDebug-tracel-tool', '0.1');

        $this->add(new Command\Translate());
    }
}
