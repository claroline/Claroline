<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Group")
 * @Route("/group")
 */
class GroupController extends AbstractCrudController
{
    public function getName()
    {
        return 'group';
    }

    use HasUsersTrait;
    use HasRolesTrait;
    use HasOrganizationsTrait;
}
