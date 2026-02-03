<?php

namespace Icap\LessonBundle\Repository;

use Claroline\AppBundle\Repository\ViewerRepositoryInterface;
use Claroline\AppBundle\Repository\ViewerRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class ChapterViewRepository extends EntityRepository implements ViewerRepositoryInterface
{
    use ViewerRepositoryTrait;

    protected static function getSubjectProp(): string
    {
        return 'chapter';
    }
}
