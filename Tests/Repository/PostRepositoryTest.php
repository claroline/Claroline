<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class PostRepositoryTest extends MockeryTestCase
{
    public function testgetCombinations()
    {
        $postRepository = m::mock('\ICAP\BlogBundle\Repository\PostRepository');

        $searchWords = 'bonjour comment allez vous';

        $searchWordsCombination = array(
            'bonjour comment allez vous',
            'bonjour comment allez',
            'bonjour comment',
            'bonjour',
            'comment',
            'allez',
            'vous'
        );

        $this->assertEquals($searchWordsCombination, $postRepository->getCombinations($searchWords));
    }
}
