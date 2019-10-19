<?php

namespace XDebugTraceTool\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XDebugTraceTool\Reader;
use XDebugTraceTool\Writer;

class Translate extends Console\Command\Command
{
    protected $source;

    protected $output;

    /** @inheritDoc */
    protected function configure()
    {
        $this
            ->setName('translate')
            ->setDescription('Translates source trace file to xml')
            ->addArgument(
                'source',
                Console\Input\InputArgument::REQUIRED,
                'Path to trace file'
            )
            ->addOption(
                'output',
                'o',
                Console\Input\InputOption::VALUE_REQUIRED,
                'Output destination',
                'php://output'
            );
    }

    /** @inheritDoc */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->source = $input->getArgument('source');
        if(!is_file($this->source)) {
            throw new Console\Exception\InvalidArgumentException('Source is not a file');
        }
        if(!is_readable($this->source)) {
            throw new Console\Exception\InvalidArgumentException('Source file is not readable');
        }

        $this->output = $input->getOption('output');
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $f = fopen($this->source, 'r+');

        $reader = new Reader\PlainParser($f, true);

        $writer = new Writer\XmlTree($this->output);
        $writer->start();

        foreach ($reader->read() as $data) {
            $writer->writeTrace($data, $data['depth']);
        }

        $writer->finish();
    }
}
