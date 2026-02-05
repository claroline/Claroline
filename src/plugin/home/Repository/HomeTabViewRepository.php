<?php

namespace Claroline\HomeBundle\Repository;

use Claroline\AppBundle\Repository\ViewerRepositoryInterface;
use Claroline\AppBundle\Repository\ViewerRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class HomeTabViewRepository extends EntityRepository implements ViewerRepositoryInterface
{
    use ViewerRepositoryTrait;

    protected static function getSubjectProp(): string
    {
        return 'homeTab';
    }
}
