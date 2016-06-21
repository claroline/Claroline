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

interface FeedReaderInterface
{
    /**
     * Returns whether a feed format is supported.
     *
     * @param string $feedType
     *
     * @return bool
     */
    public function supports($feedType);

    /**
     * Sets the feed content.
     *
     * @param SimpleXMLElement $feed
     */
    public function setFeed(SimpleXMLElement $feed);

    /**
     * Returns the feed information.
     *
     * @return FeedInfo
     */
    public function getFeedInfo();

    /**
     * Returns the feed items.
     *
     * @param int $max The maximum number of items to return
     *
     * @return array[FeedItem]
     */
    public function getFeedItems($max = null);
}
