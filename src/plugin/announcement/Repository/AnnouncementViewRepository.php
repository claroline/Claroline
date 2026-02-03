<?php

namespace Claroline\AnnouncementBundle\Repository;

use Claroline\AppBundle\Repository\ViewerRepositoryInterface;
use Claroline\AppBundle\Repository\ViewerRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class AnnouncementViewRepository extends EntityRepository implements ViewerRepositoryInterface
{
    use ViewerRepositoryTrait;

    protected static function getSubjectProp(): string
    {
        return 'announcement';
    }
}
