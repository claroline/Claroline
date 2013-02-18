<?php

namespace Claroline\RssReaderBundle\Library;

use \SimpleXMLElement;

interface FeedReaderInterface
{
    /**
     * Returns whether a feed format is supported.
     *
     * @param string $feedType
     * @return boolean
     */
    function supports($feedType);

    /**
     * Sets the feed content.
     *
     * @param SimpleXMLElement $feed
     */
    function setFeed(SimpleXMLElement $feed);

    /**
     * Returns the feed information.
     *
     * @return FeedInfo
     */
    function getFeedInfo();

    /**
     * Returns the feed items.
     *
     * @param integer $max The maximum number of items to return
     * @return array[FeedItem]
     */
    function getFeedItems($max = null);
}