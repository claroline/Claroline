<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\AppBundle\Repository\ViewerRepositoryInterface;
use Claroline\AppBundle\Repository\ViewerRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class WorkspaceViewRepository extends EntityRepository implements ViewerRepositoryInterface
{
    use ViewerRepositoryTrait;

    protected static function getSubjectProp(): string
    {
        return 'workspace';
    }
}
