<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Library\Reader;

use SimpleXMLElement;
use Claroline\RssReaderBundle\Library\FeedReaderInterface;
use Claroline\RssReaderBundle\Library\FeedInfo;
use Claroline\RssReaderBundle\Library\FeedItem;

/**
 * Reader for classic rss feeds.
 */
class RssReader implements FeedReaderInterface
{
    private $feed;

    /**
     * {@inheritdoc}
     */
    public function supports($feedType)
    {
        return $feedType === 'rss';
    }

    /**
     * {@inheritdoc}
     */
    public function setFeed(SimpleXMLElement $feed)
    {
        $this->feed = $feed;
    }

    /**
     * {@inheritdoc}
     */
    public function getFeedInfo()
    {
        $feedInfo = new FeedInfo();
        $feedInfo->setTitle($this->feed->channel->title);
        $feedInfo->setDescription($this->feed->channel->description);
        $feedInfo->setImageUrl($this->feed->channel->image->url);
        $feedInfo->setLink($this->feed->channel->link);
        $feedInfo->setLanguage($this->feed->channel->language);
        $feedInfo->setEmail($this->feed->channel->author);
        $feedInfo->setAuthor($this->feed->channel->managingEditor);
        $feedInfo->setLastUpdate($this->feed->channel->lastBuildDate);
        $feedInfo->setCopyright($this->feed->channel->copyright);
        $feedInfo->setGenerator($this->feed->channel->generator);

        return $feedInfo;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Add support for enclosures
     */
    public function getFeedItems($max = null)
    {
        $itemCount = count($this->feed->channel->item);
        $max = is_integer($max) && $max < $itemCount ? $max : $itemCount;
        $items = array();

        for ($i = 0; $i < $max; ++$i) {
            $item = new FeedItem();
            $item->setTitle($this->feed->channel->item[$i]->title);
            $item->setDescription($this->feed->channel->item[$i]->description);
            $item->setLink($this->feed->channel->item[$i]->link);
            $item->setAuthor($this->feed->channel->item[$i]->author);
            $item->setDate($this->feed->channel->item[$i]->pubDate);
            $item->setGuid($this->feed->channel->item[$i]->guid);
            $items[] = $item;
        }

        return $items;
    }
}
