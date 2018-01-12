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

use Claroline\CoreBundle\Annotations\ApiMeta;
use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\Controller\APINew\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Role")
 * @Route("/role")
 */
class RoleController extends AbstractCrudController
{
    public function getName()
    {
        return 'role';
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            'list' => [Options::SERIALIZE_COUNT_USER],
            'get' => [Options::SERIALIZE_COUNT_USER],
        ];
    }

    use HasUsersTrait;
    use HasGroupsTrait;
}
