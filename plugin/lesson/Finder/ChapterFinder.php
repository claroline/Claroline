<?php

namespace Icap\LessonBundle\Finder;

use Claroline\AppBundle\API\FinderInterface;

/**
 * @DI\Service("claroline.api.finder.lesson.chapter")
 * @DI\Tag("claroline.finder")
 */
class ChapterFinder implements FinderInterface
{
    public function getClass()
    {
        return 'Icap\LessonBundle\Entity\Chapter';
    }
}
