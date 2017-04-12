<?php

namespace UJM\ExoBundle\Transfer\Parser;

use Claroline\CoreBundle\Library\Transfert\RichTextFormatter;

class ImportContentParser implements ContentParserInterface
{
    /**
     * Path to content files to format.
     *
     * @var string
     */
    private $injectPath;

    /**
     * @var RichTextFormatter
     */
    private $formatter;

    /**
     * ContentParser constructor.
     *
     * @param string            $injectPath
     * @param RichTextFormatter $formatter
     */
    public function __construct(
        $injectPath,
        RichTextFormatter $formatter)
    {
        $this->injectPath = $injectPath;
        $this->formatter = $formatter;
    }

    public function parse($content)
    {
        if (!empty($content)) {
            $textPath = $this->injectPath.DIRECTORY_SEPARATOR.$content;
            $text = file_get_contents($textPath);

            return $this->formatter->format($text);
        }

        return $content;
    }
}
