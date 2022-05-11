<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/group")
 */
class GroupController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasRolesTrait;
    use HasOrganizationsTrait;
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var MailManager */
    private $mailManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        MailManager $mailManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->mailManager = $mailManager;
    }

    public function getName()
    {
        return 'group';
    }

    public function getClass()
    {
        return Group::class;
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list"}
     * )
     */
    public function listAction(Request $request, $class): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        return parent::listAction($request, $class);
    }

    /**
     * @Route("/list/managed", name="apiv2_group_list_managed", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list"}
     * )
     */
    public function listManagedAction(User $user, Request $request): JsonResponse
    {
        $filters = [];
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $filters['organization'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }

        return new JsonResponse($this->finder->search(
            Group::class,
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
     * @Route("/password/reset", name="apiv2_group_initialize_password", methods={"POST"})
     */
    public function resetPasswordAction(Request $request): JsonResponse
    {
        /** @var Group[] $groups */
        $groups = $this->decodeIdsString($request, Group::class);

        $this->om->startFlushSuite();
        $i = 0;
        foreach ($groups as $group) {
            foreach ($group->getUsers() as $user) {
                if ($this->authorization->isGranted('ADMINISTRATE', $user)) {
                    $this->mailManager->sendForgotPassword($user);
                    ++$i;
                }

                if (0 === $i % 200) {
                    $this->om->forceFlush();
                }
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    protected function getDefaultHiddenFilters()
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();

            return [
                'organization' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations()),
            ];
        }

        return [];
    }
}
