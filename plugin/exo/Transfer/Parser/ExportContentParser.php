<?php

namespace UJM\ExoBundle\Transfer\Parser;

use Claroline\CoreBundle\Library\Transfert\RichTextFormatter;

class ExportContentParser implements ContentParserInterface
{
    /**
     * @var string
     */
    private $dumpPath;

    private $dumpedContents = [];

    /**
     * @var RichTextFormatter
     */
    private $formatter;

    /**
     * ContentParser constructor.
     *
     * @param string            $dumpPath
     * @param RichTextFormatter $formatter
     */
    public function __construct(
        $dumpPath,
        RichTextFormatter $formatter)
    {
        $this->dumpPath = $dumpPath;
        $this->formatter = $formatter;
    }

    public function getDumpedContents()
    {
        return $this->dumpedContents;
    }

    public function parse($content)
    {
        if (!empty($content)) {
            $uid = uniqid().'.txt';
            $path = $this->dumpPath.DIRECTORY_SEPARATOR.$uid;

            file_put_contents($path, $content);

            $this->dumpedContents[$uid] = $path;

            return $uid;
        }

        return $content;
    }
}
