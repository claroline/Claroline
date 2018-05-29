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

class ReaderProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetReaderThrowsAnExceptionIfFormatIsUnknown()
    {
        $this->expectException('Claroline\RssReaderBundle\Library\UnknownFormatException');
        $parser = new ReaderProvider([]);
        $parser->getReaderFor('<unknown/>');
    }

    public function testGetReaderReturnsAReaderForTheFeedIfFormatIsSupported()
    {
        $mockReader = $this->createMock('Claroline\RssReaderBundle\Library\FeedReaderInterface');
        $mockReader->expects($this->any())
            ->method('supports')
            ->with('someFormat')
            ->will($this->returnValue(true));
        $parser = new ReaderProvider([$mockReader]);
        $reader = $parser->getReaderFor('<someFormat/>');
        $this->assertEquals($mockReader, $reader);
    }
}
