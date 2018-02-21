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
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * List platform roles.
     *
     * @Route("platform/roles", name="apiv2_platform_roles_list")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listPlatformRolesAction(Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Role', array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['type' => 1]]
            ))
        );
    }

    use HasUsersTrait;
    use HasGroupsTrait;
}
