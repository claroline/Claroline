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
 * Reader for atom feeds.
 */
class AtomReader implements FeedReaderInterface
{
    private $feed;

    /**
     * {@inheritdoc}
     */
    public function supports($feedType)
    {
        return $feedType === 'feed';
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
        $feedInfo->setTitle($this->feed->title);
        $feedInfo->setDescription($this->feed->subtitle);
        $feedInfo->setImageUrl($this->feed->logo);
        $feedInfo->setLink($this->feed->link['href']);
        $feedInfo->setLanguage($this->feed->language);
        $feedInfo->setEmail($this->feed->author->email);
        $feedInfo->setAuthor($this->feed->author->name);
        $feedInfo->setLastUpdate($this->feed->updated);
        $feedInfo->setCopyright($this->feed->rights);
        $feedInfo->setGenerator($this->feed->generator);

        return $feedInfo;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Add support for enclosures
     */
    public function getFeedItems($max = null)
    {
        $itemCount = count($this->feed->entry);
        $max = is_integer($max) && $max < $itemCount ? $max : $itemCount;
        $items = array();

        for ($i = 0; $i < $max; ++$i) {
            $item = new FeedItem();
            $item->setTitle($this->feed->entry[$i]->title);
            $item->setDescription(
                $this->feed->entry[$i]->summary ?: $this->feed->entry[$i]->content
            );
            $item->setLink($this->feed->entry[$i]->link['href']);
            $item->setAuthor($this->feed->entry[$i]->author->name);
            $item->setDate($this->feed->entry[$i]->updated);
            $item->setGuid($this->feed->entry[$i]->id);
            $items[] = $item;
        }

        return $items;
    }
}
