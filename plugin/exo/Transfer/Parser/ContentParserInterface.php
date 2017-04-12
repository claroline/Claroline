<?php

namespace UJM\ExoBundle\Transfer\Parser;

interface ContentParserInterface
{
    /**
     * @param string $content - the content string to parse
     *
     * @return string - the parsed content
     */
    public function parse($content);
}
