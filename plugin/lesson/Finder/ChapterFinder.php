<?php

namespace Icap\LessonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;

/**
 * @DI\Service("claroline.api.finder.lesson.chapter")
 * @DI\Tag("claroline.finder")
 */
class ChapterFinder extends AbstractFinder
{
    public function getClass()
    {
        return 'Icap\LessonBundle\Entity\Chapter';
    }
}
