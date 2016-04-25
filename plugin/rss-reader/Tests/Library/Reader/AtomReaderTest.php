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

class AtomReaderTest extends \PHPUnit_Framework_TestCase
{
    private $reader;

    protected function setUp()
    {
        $this->reader = new AtomReader();
        $this->reader->setFeed(
            new SimpleXMLElement(file_get_contents(__DIR__.'/../../Stub/files/feed_burner.atom'))
        );
    }

    public function testGetFeedInfo()
    {
        $feedInfo = $this->reader->getFeedInfo();
        $this->assertInstanceOf('Claroline\RssReaderBundle\Library\FeedInfo', $feedInfo);
        $this->assertEquals('PTCC - Pacific Tropical Cyclone Centre', $feedInfo->getTitle());
        $this->assertContains('An unofficial weather website', $feedInfo->getDescription());
        $this->assertEquals(null, $feedInfo->getImageUrl());
        $this->assertEquals('http://www.pacificstorms.org', $feedInfo->getLink());
        $this->assertEquals(null, $feedInfo->getLanguage());
        $this->assertEquals(null, $feedInfo->getEmail());
        $this->assertEquals(null, $feedInfo->getAuthor());
        $this->assertEquals('2013-02-08T12:04:33Z', $feedInfo->getLastUpdate());
        $this->assertEquals(null, $feedInfo->getCopyright());
        $this->assertEquals('WordPress', $feedInfo->getGenerator());
    }

    public function testGetFeedItems()
    {
        // all the items should be returned by default
        $this->assertEquals(10, count($this->reader->getFeedItems()));
        $this->assertEquals(10, count($this->reader->getFeedItems(20)));
        $feedItems = $this->reader->getFeedItems(5);
        $this->assertEquals(5, count($feedItems));
        $this->assertInstanceOf('Claroline\RssReaderBundle\Library\FeedItem', $feedItems[0]);
        $this->assertEquals('Another severe earthquake&#8230;', $feedItems[0]->getTitle());
        $this->assertContains('3 severe earthquakes around Santa Cruz', $feedItems[0]->getDescription());
        $this->assertEquals(
            'http://www.pacificstorms.org/index.php/2013/02/08/another-severe-earthquake/',
            $feedItems[0]->getLink()
        );
        $this->assertEquals('Jams', $feedItems[0]->getAuthor());
        $this->assertEquals('2013-02-08T12:04:33Z', $feedItems[0]->getDate());
        $this->assertEquals('http://www.pacificstorms.org/?p=2503', $feedItems[0]->getGuid());
    }
}
