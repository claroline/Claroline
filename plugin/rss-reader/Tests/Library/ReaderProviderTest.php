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

class ReaderProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetReaderThrowsAnExceptionIfFormatIsUnknown()
    {
        $this->setExpectedException('Claroline\RssReaderBundle\Library\UnknownFormatException');
        $parser = new ReaderProvider(array());
        $parser->getReaderFor('<unknown/>');
    }

    public function testGetReaderReturnsAReaderForTheFeedIfFormatIsSupported()
    {
        $mockReader = $this->getMock('Claroline\RssReaderBundle\Library\FeedReaderInterface');
        $mockReader->expects($this->any())
            ->method('supports')
            ->with('someFormat')
            ->will($this->returnValue(true));
        $parser = new ReaderProvider(array($mockReader));
        $reader = $parser->getReaderFor('<someFormat/>');
        $this->assertEquals($mockReader, $reader);
    }
}
