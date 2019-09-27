<?php

namespace Icap\LessonBundle\Tests\Controller;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Icap\LessonBundle\Testing\Persister;

class ApiControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persist;
    /** @var LessonChapterManager */
    private $chapterManager;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->chapterManager = $container->get('Icap\LessonBundle\Manager\ChapterManager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
    }

    public function testGetChapters()
    {
        $user = $this->persist->user('david');
        $lesson = $this->persist->lesson('Test lesson', $user);
        $this->persist->chapter('Test title', 'Test text', $lesson, $lesson->getRoot());

        $this->assertTrue(!is_null($lesson));
    }
}
