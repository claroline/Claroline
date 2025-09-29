<?php

namespace Claroline\EvaluationBundle\Repository\Sequence;

use Claroline\AppBundle\Repository\ViewerRepositoryInterface;
use Claroline\AppBundle\Repository\ViewerRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class SequenceViewRepository extends EntityRepository implements ViewerRepositoryInterface
{
    use ViewerRepositoryTrait;

    protected static function getSubjectProp(): string
    {
        return 'sequence';
    }
}
