<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Library;

use SimpleXMLElement;

/**
 * Provides a reader for a feed, choosing amongst a set of predefined readers.
 */
class ReaderProvider
{
    private $readers;

    /**
     * Constructor.
     *
     * @param array[FeedReaderInterface] $readers
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * Returns a reader object for the given feed.
     *
     * @param string $feedContent
     *
     * @return FeedReaderInterface
     *
     * @throws UnknownFormatException if the feed format is not supported by any reader
     */
    public function getReaderFor($feedContent)
    {
        @$content = new SimpleXMLElement($feedContent);

        foreach ($this->readers as $reader) {
            if ($reader->supports($content->getName())) {
                $reader->setFeed($content);

                return $reader;
            }
        }

        throw new UnknownFormatException(
            "Parser has no support for '{$content->getName()}' feed format"
        );
    }
}
