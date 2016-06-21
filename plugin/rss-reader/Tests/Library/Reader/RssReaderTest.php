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

class RssReaderTest extends \PHPUnit_Framework_TestCase
{
    private $reader;

    protected function setUp()
    {
        $this->reader = new RssReader();
        $this->reader->setFeed(
            new SimpleXMLElement(file_get_contents(__DIR__.'/../../Stub/files/soir.rss'))
        );
    }

    public function testGetFeedInfo()
    {
        $feedInfo = $this->reader->getFeedInfo();
        $this->assertInstanceOf('Claroline\RssReaderBundle\Library\FeedInfo', $feedInfo);
        $this->assertEquals('Crise politique - www.lesoir.be', $feedInfo->getTitle());
        $this->assertEquals(
            "Flux d'informations de la catégorie crise politique du site www.lesoir.be",
            $feedInfo->getDescription()
        );
        $this->assertEquals('http://www.lesoir.be//img/ui/fb-image.png', $feedInfo->getImageUrl());
        $this->assertEquals('http://www.lesoir.be/feed/17/destination_principale_block', $feedInfo->getLink());
        $this->assertEquals('fr', $feedInfo->getLanguage());
        $this->assertEquals(null, $feedInfo->getEmail());
        $this->assertEquals('internet@lesoir.be', $feedInfo->getAuthor());
        $this->assertEquals('Thu, 14 Feb 2013 09:53:48 +0100', $feedInfo->getLastUpdate());
        $this->assertEquals('Sudpresse - 2013', $feedInfo->getCopyright());
        $this->assertEquals('Lesoir - 2.0', $feedInfo->getGenerator());
    }

    public function testGetFeedItems()
    {
        // all the items should be returned by default
        $this->assertEquals(20, count($this->reader->getFeedItems()));
        $this->assertEquals(20, count($this->reader->getFeedItems(30)));
        $feedItems = $this->reader->getFeedItems(10);
        $this->assertEquals(10, count($feedItems));
        $this->assertInstanceOf('Claroline\RssReaderBundle\Library\FeedItem', $feedItems[0]);
        $this->assertEquals('MR: Miller à la tête du Centre Jean Gol', $feedItems[0]->getTitle());
        $this->assertContains('Tous les partis projettent des Congrès', $feedItems[0]->getDescription());
        $this->assertContains('http://www.lesoir.be/188034/article', $feedItems[0]->getLink());
        $this->assertEquals('David Coppi', $feedItems[0]->getAuthor());
        $this->assertEquals('Tue, 12 Feb 2013 10:51:26 +0100', $feedItems[0]->getDate());
        $this->assertEquals('188034 at http://www.lesoir.be', $feedItems[0]->getGuid());
    }
}
