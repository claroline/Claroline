<?php

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