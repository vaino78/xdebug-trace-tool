<?php

namespace XDebugTraceTool\Writer;

class XmlTree
{
    protected $xml;

    protected $beginDepth;

    protected $lastDepth;

    public function __construct(string $uri)
    {
        $this->xml = $this->createXmlHandler($uri);
    }

    public function start(): void
    {
        $this->xml->startDocument('1.0', 'utf-8');
        $this->xml->startElement('trace');
    }

    public function writeTrace(array $attrData, $depth): void
    {
        if(!isset($this->beginDepth)) {
            $this->beginDepth = $depth;
        }

        if(isset($this->lastDepth)) {
            $closing = ($this->lastDepth - $depth - $this->beginDepth + 1);
            if($closing < 0) {
                $this->openTags($closing*-1, 't');
            } elseif ($closing > 0) {
                $this->closeTags($closing);
            }
        }

        $this->xml->startElement('t');
        foreach($attrData as $attr => $datum) {
            $this->xml->writeAttribute($attr, $datum);
        }

        $this->lastDepth = $depth;

        static $i;
        if(!isset($i)) {
            $i = 0;
        }
        if($i++ % 1000) {
            $this->xml->flush();
            flush();
        }
    }

    /**
     * @param int $count
     * @param string $tagName
     */
    protected function openTags(int $count, string $tagName): void
    {
        while($count--) {
            $this->xml->startElement($tagName);
        }
    }

    /**
     * @param int $count
     */
    protected function closeTags(int $count = 0)
    {
        while($count--) {
            $this->xml->endElement();
        }
    }

    public function finish(): void
    {
        if(isset($this->beginDepth) && isset($this->lastDepth)) {
            $this->closeTags($this->lastDepth - $this->beginDepth + 1);
        }

        $this->xml->fullEndElement();
        $this->xml->endDocument();

        $this->xml->flush();
        flush();
    }

    protected function createXmlHandler($uri): \XMLWriter
    {
        $xml = new \XMLWriter();
        $xml->openUri($uri);
        $xml->setIndent(true);

        return $xml;
    }
}
