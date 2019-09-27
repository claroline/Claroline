<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Workspace;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceUserQueueManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/workspace")
 */
class RegistrationController extends AbstractApiController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ObjectManager */
    protected $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var FinderProvider */
    private $finder;

    /** @var Crud */
    private $crud;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var WorkspaceUserQueueManager */
    private $registrationQueueManager;

    /**
     * RegistrationController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param SerializerProvider            $serializer
     * @param FinderProvider                $finder
     * @param Crud                          $crud
     * @param WorkspaceManager              $workspaceManager
     * @param WorkspaceUserQueueManager     $registrationQueueManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        FinderProvider $finder,
        Crud $crud,
        WorkspaceManager $workspaceManager,
        WorkspaceUserQueueManager $registrationQueueManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->workspaceManager = $workspaceManager;
        $this->registrationQueueManager = $registrationQueueManager;
    }

    /**
     * @ApiDoc(
     *     description="List the user registration pending for workspace.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\User",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         {"name": "id",  "type": {"string", "integer"}, "description": "The workspace id or uuid"}
     *     }
     * )
     * @EXT\Route(
     *    "/{id}/user/pending",
     *    name="apiv2_workspace_list_pending"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function listPendingAction(Request $request, Workspace $workspace)
    {
        return new JsonResponse($this->finder->search(
            WorkspaceRegistrationQueue::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Validate user registration pending for workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of user uuids."}
     *     },
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @EXT\Route(
     *    "/{id}/registration/validate",
     *    name="apiv2_workspace_registration_validate"
     * )
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function validateRegistrationAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $users = $this->om->findList(User::class, 'uuid', $query['ids']);

        foreach ($users as $user) {
            /** @var WorkspaceRegistrationQueue $pending */
            $pending = $this->om->getRepository(WorkspaceRegistrationQueue::class)
                ->findOneBy(['user' => $user, 'workspace' => $workspace]);
            //maybe use the crud instead ? I don't know yet
            $this->registrationQueueManager->validateRegistration($pending);
            $this->registrationQueueManager->removeRegistration($pending);
        }

        return new JsonResponse($this->finder->search(
            WorkspaceRegistrationQueue::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Remove user registration pending for workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of user uuids."}
     *     },
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @EXT\Route(
     *    "/{id}/registration/remove",
     *    name="apiv2_workspace_registration_remove"
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function removeRegistrationAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $users = $this->om->findList(User::class, 'uuid', $query['ids']);

        foreach ($users as $user) {
            /** @var WorkspaceRegistrationQueue $pending */
            $pending = $this->om->getRepository(WorkspaceRegistrationQueue::class)
                ->findOneBy(['user' => $user, 'workspace' => $workspace]);
            $this->registrationQueueManager->removeRegistration($pending);
        }

        return new JsonResponse($this->finder->search(
            WorkspaceRegistrationQueue::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Unregister users from workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of user uuids."}
     *     },
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @EXT\Route(
     *    "/{id}/users/unregistrate",
     *    name="apiv2_workspace_unregister_users"
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function unregisterUsersAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $users = $this->om->findList(User::class, 'uuid', $query['ids']);

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->workspaceManager->unregister($user, $workspace);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Unregister groups from workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of group uuids."}
     *     },
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @EXT\Route(
     *    "/{id}/groups/unregistrate",
     *    name="apiv2_workspace_unregister_groups"
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function unregisterGroupsAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $groups = $this->om->findList(Group::class, 'uuid', $query['ids']);

        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $this->workspaceManager->unregister($group, $workspace);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Register users in different workspaces.",
     *     queryString={
     *         {"name": "users", "type": "array", "description": "The list of user uuids."},
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         {"name": "role", "type": {"string"}, "description": "The role translation key"}
     *     }
     * )
     * @EXT\Route(
     *    "/users/register/bulk/{role}",
     *    name="apiv2_workspace_bulk_register_users"
     * )
     * @EXT\Method("PATCH")
     *
     * @param string  $role
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkRegisterUsersAction($role, Request $request)
    {
        $workspaces = $this->decodeIdsString($request, Workspace::class, 'workspaces');
        $users = $this->decodeIdsString($request, User::class, 'users');

        foreach ($workspaces as $workspace) {
            $roleEntity = $this->om->getRepository(Role::class)
                ->findOneBy(['translationKey' => $role, 'workspace' => $workspace]);

            $this->crud->patch($roleEntity, 'user', Crud::COLLECTION_ADD, $users);
        }

        return new JsonResponse(array_map(function ($workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @ApiDoc(
     *     description="Register users in different workspaces.",
     *     queryString={
     *         {"name": "groups", "type": "array", "description": "The list of group uuids."},
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         {"name": "role", "type": {"string"}, "description": "The role translation key"}
     *     }
     * )
     * @EXT\Route(
     *    "/groups/register/bulk/{role}",
     *    name="apiv2_workspace_bulk_register_groups"
     * )
     * @EXT\Method("PATCH")
     *
     * @param string  $role
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkRegisterGroupsAction($role, Request $request)
    {
        $workspaces = $this->decodeIdsString($request, Workspace::class, 'workspaces');
        $groups = $this->decodeIdsString($request, Group::class, 'groups');

        foreach ($workspaces as $workspace) {
            $roleEntity = $this->om->getRepository(Role::class)
                ->findOneBy(['translationKey' => $role, 'workspace' => $workspace]);

            $this->crud->patch($roleEntity, 'group', Crud::COLLECTION_ADD, $groups);
        }

        return new JsonResponse(array_map(function ($workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @ApiDoc(
     *     description="Register a user in a list of workspace.",
     *     queryString={
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         {"name": "user", "type": {"string"}, "description": "The user uuid"}
     *     }
     * )
     * @EXT\Route("/register/{user}", name="apiv2_workspace_register")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function registerAction(User $user, Request $request)
    {
        // If user is admin or registration validation is disabled, subscribe user
        //see WorkspaceParametersController::userSubscriptionAction
        /** @var Workspace[] $workspaces */
        $workspaces = $this->decodeIdsString($request, Workspace::class, 'workspaces');

        foreach ($workspaces as $workspace) {
            if ($this->authorization->isGranted('ROLE_ADMIN') || !$workspace->getRegistrationValidation()) {
                $this->workspaceManager->addUser($workspace, $user);
            } else {
                // Otherwise add user to validation queue if not already there
                if (!$this->workspaceManager->isUserInValidationQueue($workspace, $user)) {
                    $this->workspaceManager->addUserQueue($workspace, $user);
                }
            }
        }

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @ApiDoc(
     *     description="Unregister a user from a list of workspace.",
     *     queryString={
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         {"name": "user", "type": {"string"}, "description": "The user uuid"}
     *     }
     * )
     * @EXT\Route("/unregister/{user}", name="apiv2_workspace_unregister")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function unregisterAction(User $user, Request $request)
    {
        $workspaces = $this->decodeIdsString($request, Workspace::class, 'workspaces');

        foreach ($workspaces as $workspace) {
            $this->workspaceManager->unregister($user, $workspace);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Self-register to a workspace that allows it.",
     *     parameters={
     *         {"name": "workspace", "type": {"string"}, "description": "The workspace uuid"}
     *     }
     * )
     * @EXT\Route(
     *     "/{workspace}/register/self",
     *     name="apiv2_workspace_self_register"
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *     "workspace",
     *     class = "ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Workspace $workspace
     * @param User      $currentUser
     *
     * @return JsonResponse
     */
    public function selfRegisterAction(Workspace $workspace, User $currentUser)
    {
        if (!$workspace->getSelfRegistration()) {
            throw new AccessDeniedException();
        }
        if (!$workspace->getRegistrationValidation()) {
            $this->workspaceManager->addUser($workspace, $currentUser);
        } elseif (!$this->workspaceManager->isUserInValidationQueue($workspace, $currentUser)) {
            $this->workspaceManager->addUserQueue($workspace, $currentUser);
        }

        return new JsonResponse();
    }
}
