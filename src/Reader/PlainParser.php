<?php

namespace XDebugTraceTool\Reader;

class PlainParser
{
    /** @var resource */
    protected $handler;

    /** @var bool */
    protected $skipNoMatch;

    /** @var string */
    protected $separator;

    /** @var int */
    protected $depthLength;

    /** @var string */
    protected $expression;

    public function __construct($handler, bool $skipNoMatch = false, string $separator = '   ', int $depthLength = 2)
    {
        $this->handler = $handler;
        $this->skipNoMatch = $skipNoMatch;
        $this->separator = $separator;
        $this->depthLength = $depthLength;
    }

    public function read(): \Generator
    {
        $expression = $this->getParseExpression();

        while(($raw = $this->getRawString()) !== false) {
            $data = $this->extractData($raw, $expression);
            if(!$data) {
                if(!$this->skipNoMatch) {
                    throw new \RuntimeException(sprintf('Incorrect incoming data: %s', $raw));
                }
                continue;
            }

            yield $data;
        }
    }

    /**
     * @return false|string
     */
    protected function getRawString()
    {
        return fgets($this->handler);
    }

    /**
     * @param string|null $rawData
     * @param string $expression
     * @return array|null
     */
    protected function extractData($rawData, &$expression): ?array
    {
        $m = [];
        if(!preg_match($expression, $rawData, $m)) {
            return null;
        }

        return [
            'time' => $this->parseTime($m[1]),
            'mem' => $this->parseMem($m[2]),
            'depth' => $this->parseDepth($m[3]),
            'func' => $this->parseFunc($m[4]),
            'ref' => $this->parseRef($m[5])
        ];
    }

    /**
     * @param string $rawTime
     * @return float
     */
    protected function parseTime($rawTime): float
    {
        return floatval($rawTime);
    }

    /**
     * @param string $rawMem
     * @return int
     */
    protected function parseMem($rawMem): int
    {
        return intval(trim($rawMem));
    }

    /**
     * @param string $rawDepth
     * @return int
     */
    protected function parseDepth($rawDepth): int
    {
        return strlen($rawDepth) / $this->depthLength;
    }

    /**
     * @param string $rawFunc
     * @return string
     */
    protected function parseFunc($rawFunc): string
    {
        return trim($rawFunc);
    }

    /**
     * @param string $rawRef
     * @return string
     */
    protected function parseRef($rawRef): string
    {
        return trim($rawRef);
    }

    /**
     * @return string
     */
    protected function getParseExpression(): string
    {
        if(empty($this->expression)) {
            $this->expression = $this->buildParseExpression();
        }
        return $this->expression;
    }

    /**
     * @return string
     */
    protected function buildParseExpression(): string
    {
        return sprintf(
            '@^\s+([\d.]+)%1$s(\s*\d+)%1$s(\s*?)\-> (\S+?) (\S+?)$@i',
            $this->separator
        );
    }
}
