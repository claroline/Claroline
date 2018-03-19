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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @Route(
     *    "/list/registerable",
     *    name="apiv2_group_list_registerable"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function listRegisterableGroupAction(User $user, Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Group',
            array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['organization' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations())]]
            )
        ));
    }

    /**
     * @Route(
     *    "/list/managed",
     *    name="apiv2_group_list_managed"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function listManagedAction(User $user, Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Group',
            array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['organization' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getAdministratedOrganizations()->toArray())]]
            )
        ));
    }

    use HasUsersTrait;
    use HasRolesTrait;
    use HasOrganizationsTrait;
}
