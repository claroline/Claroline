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

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\ForgotPasswordEvent;
use Claroline\CoreBundle\Event\Log\UserDisableEvent;
use Claroline\CoreBundle\Event\Log\UserEnableEvent;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/user")
 */
class UserController extends AbstractCrudController
{
    use PermissionCheckerTrait;
    use HasRolesTrait;
    use HasOrganizationsTrait;
    use HasGroupsTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var StrictDispatcher */
    private $eventDispatcher;
    /** @var UserManager */
    private $manager;
    /** @var MailManager */
    private $mailManager;
    /** @var WorkspaceManager */
    private $workspaceManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        UserManager $manager,
        MailManager $mailManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
        $this->mailManager = $mailManager;
        $this->workspaceManager = $workspaceManager;
    }

    public function getName()
    {
        return 'user';
    }

    public function getClass()
    {
        return User::class;
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
     * @ApiDoc(
     *     description="Get the list of user in that share the current user managed organizations (and sub organizations).",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\User&!recursiveOrXOrganization",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @Route("/list/managed", name="apiv2_user_list_managed", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function listManagedOrganizationAction(User $user, Request $request): JsonResponse
    {
        $filters = $this->authorization->isGranted('ROLE_ADMIN') ?
            [] :
            [
                'recursiveOrXOrganization' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getAdministratedOrganizations()->toArray()),
            ];

        return new JsonResponse($this->finder->search(
            User::class,
            array_merge($request->query->all(), ['hiddenFilters' => $filters])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Create the personal workspaces of an array of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @Route("/pws", name="apiv2_users_pws_create", methods={"POST"})
     */
    public function createPersonalWorkspaceAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if (!$user->getPersonalWorkspace() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->workspaceManager->setPersonalWorkspace($user);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    /**
     * @ApiDoc(
     *     description="Remove the personal workspaces of an array of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @Route("/pws", name="apiv2_users_pws_delete", methods={"DELETE"})
     */
    public function deletePersonalWorkspaceAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            $personalWorkspace = $user->getPersonalWorkspace();
            if ($personalWorkspace && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->crud->delete($personalWorkspace);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    /**
     * @Route("/{keep}/{remove}/merge", name="apiv2_user_merge", methods={"PUT"})
     * @EXT\ParamConverter("keep", options={"mapping": {"keep": "uuid"}})
     * @EXT\ParamConverter("remove", options={"mapping": {"remove": "uuid"}})
     */
    public function mergeUsersAction(User $keep, User $remove): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $keep, true);
        $this->checkPermission('ADMINISTRATE', $remove, true);

        // Dispatching an event for letting plugins and core do what they need to do
        /** @var MergeUsersEvent $event */
        $event = $this->eventDispatcher->dispatch(
            'merge_users',
            'User\MergeUsers',
            [
                $keep,
                $remove,
            ]
        );

        $keep_username = $keep->getUsername();
        $remove_username = $remove->getUsername();

        // Delete old user
        $this->crud->deleteBulk([$remove], [Options::SOFT_DELETE]);

        $event->addMessage("[CoreBundle] user removed: $remove_username");
        $event->addMessage("[CoreBundle] user kept: $keep_username");

        return new JsonResponse($event->getMessages());
    }

    /**
     * @ApiDoc(
     *     description="Enable a list of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @Route("/enable", name="apiv2_users_enable", methods={"PUT"})
     */
    public function enableUsersAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if (!$user->isEnabled() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->manager->enable($user);
                $this->eventDispatcher->dispatch(UserEnableEvent::ACTION, UserEnableEvent::class, [$user]);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    /**
     * @ApiDoc(
     *     description="Disable a list of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @Route("/disable", name="apiv2_users_disable", methods={"PUT"})
     */
    public function disableUsersAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if ($user->isEnabled() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->manager->disable($user);
                $this->eventDispatcher->dispatch(UserDisableEvent::ACTION, UserDisableEvent::class, [$user]);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    /**
     * @ApiDoc(
     *     description="Reset a list of user password.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     * @Route("/password/reset", name="apiv2_users_password_reset", methods={"PUT"})
     */
    public function resetPasswordAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if ($this->checkPermission('ADMINISTRATE', $user)) {
                $this->mailManager->sendForgotPassword($user);
                $this->eventDispatcher->dispatch(ForgotPasswordEvent::ACTION, ForgotPasswordEvent::class, [$user]);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'deleteBulk' => [Options::SOFT_DELETE],
            'create' => [
                //maybe move these options in an other class
                Options::ADD_NOTIFICATIONS,
                Options::WORKSPACE_VALIDATE_ROLES,
            ],
            'get' => [Options::SERIALIZE_FACET],
            'update' => [Options::SERIALIZE_FACET],
        ]);
    }

    public function getRequirements(): array
    {
        return array_merge(parent::getRequirements(), [
          'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|csv|current|\/)).*'],
          'update' => ['id' => '^(?!.*(schema|parameters|find|doc|csv|current|\/)).*'],
          'exist' => [],
        ]);
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();

            return [
                'recursiveOrXOrganization' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations()),
            ];
        }

        return [];
    }
}
