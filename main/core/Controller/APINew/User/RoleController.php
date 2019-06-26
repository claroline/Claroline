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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
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
            'get' => [Options::SERIALIZE_COUNT_USER, Options::SERIALIZE_ROLE_DESKTOP_TOOLS],
            'create' => [Options::SERIALIZE_COUNT_USER, Options::SERIALIZE_ROLE_DESKTOP_TOOLS],
            'update' => [Options::SERIALIZE_COUNT_USER, Options::SERIALIZE_ROLE_DESKTOP_TOOLS],
        ];
    }

    /**
     * List platform roles.
     *
     * @Route("platform", name="apiv2_role_platform_list")
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

    /**
     * List platform roles.
     *
     * @Route("platform/grantable", name="apiv2_role_platform_grantable_list")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listPlatformRolesGrantableAction(Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Role', array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['type' => 1, 'grantable' => true]]
            ))
        );
    }

    /**
     * List loggable platform roles.
     *
     * @Route("platform/loggable", name="apiv2_role_platform_loggable_list")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listLoggablePlatformRolesAction(Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Role', array_merge(
                $request->query->all(),
                ['hiddenFilters' => [
                    'type' => 1,
                    'blacklist' => ['ROLE_ANONYMOUS'],
                ]]
            ))
        );
    }

    use HasUsersTrait;
    use HasGroupsTrait;

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Role';
    }
}
