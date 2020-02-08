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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/role")
 */
class RoleController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasGroupsTrait;

    public function getName()
    {
        return 'role';
    }

    /**
     * List platform roles.
     *
     * @EXT\Route("/platform", name="apiv2_role_platform_list")
     * @EXT\Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listPlatformRolesAction(Request $request)
    {
        return new JsonResponse(
            $this->finder->search(Role::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['type' => 1]]
            ))
        );
    }

    /**
     * List platform roles.
     *
     * @EXT\Route("/platform/grantable", name="apiv2_role_platform_grantable_list")
     * @EXT\Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listPlatformRolesGrantableAction(Request $request)
    {
        return new JsonResponse(
            $this->finder->search(Role::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['type' => 1, 'grantable' => true]]
            ))
        );
    }

    public function getClass()
    {
        return Role::class;
    }
}
