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
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
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
        $filters = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ?
          [] :
          ['organization' => array_map(function (Organization $organization) {
              return $organization->getUuid();
          }, $user->getOrganizations())];

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Group',
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
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
        $filters = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ?
          [] :
          ['organization' => array_map(function (Organization $organization) {
              return $organization->getUuid();
          }, $user->getAdministratedOrganizations()->toArray())];

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Group',
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
     * @Route(
     *    "/password/reset",
     *    name="apiv2_group_initialize_password"
     * )
     * @Method("POST")
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function resetPasswordAction(Request $request)
    {
        $groups = $this->decodeIdsString($request, Group::class);
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($groups as $group) {
            foreach ($group->getUsers() as $user) {
                $this->container->get('claroline.manager.user_manager')->sendResetPassword($user);
                ++$i;

                if (0 === $i % 200) {
                    $this->om->forceFlush();
                }
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse();
    }

    use HasUsersTrait;
    use HasRolesTrait;
    use HasOrganizationsTrait;

    public function getClass()
    {
        return Group::class;
    }
}
