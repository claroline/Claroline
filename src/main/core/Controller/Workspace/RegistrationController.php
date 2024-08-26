<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Workspace;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceUserQueueManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace")
 */
class RegistrationController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly WorkspaceManager $workspaceManager,
        private readonly WorkspaceUserQueueManager $registrationQueueManager,
        private readonly TokenStorageInterface $tokenStorage
    ) {
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
     *
     * @Route(
     *    "/{id}/user/pending",
     *    name="apiv2_workspace_list_pending",
     *    methods={"GET"}
     * )
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function listPendingAction(Request $request, Workspace $workspace): JsonResponse
    {
        return new JsonResponse($this->crud->list(
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
     *
     * @Route(
     *    "/{id}/registration/validate",
     *    name="apiv2_workspace_registration_validate",
     *    methods={"PATCH"}
     * )
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function validateRegistrationAction(Request $request, Workspace $workspace): JsonResponse
    {
        $query = $request->query->all();
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $query['ids']]);

        foreach ($users as $user) {
            /** @var WorkspaceRegistrationQueue $pending */
            $pending = $this->om->getRepository(WorkspaceRegistrationQueue::class)
                ->findOneBy(['user' => $user, 'workspace' => $workspace]);
            // maybe use the crud instead ? I don't know yet
            $this->registrationQueueManager->validateRegistration($pending);
            $this->registrationQueueManager->removeRegistration($pending);
        }

        return new JsonResponse($this->crud->list(
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
     *
     * @Route(
     *    "/{id}/registration/remove",
     *    name="apiv2_workspace_registration_remove",
     *    methods={"DELETE"}
     * )
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function removeRegistrationAction(Request $request, Workspace $workspace): JsonResponse
    {
        $query = $request->query->all();
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $query['ids']]);

        foreach ($users as $user) {
            /** @var WorkspaceRegistrationQueue $pending */
            $pending = $this->om->getRepository(WorkspaceRegistrationQueue::class)
                ->findOneBy(['user' => $user, 'workspace' => $workspace]);
            $this->registrationQueueManager->removeRegistration($pending);
        }

        return new JsonResponse($this->crud->list(
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
     *
     * @Route(
     *    "/{id}/users/unregister",
     *    name="apiv2_workspace_unregister_users",
     *    methods={"DELETE"}
     * )
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function unregisterUsersAction(Request $request, Workspace $workspace): JsonResponse
    {
        $query = $request->query->all();
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $query['ids']]);

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
     *
     * @Route(
     *    "/{id}/groups/unregister",
     *    name="apiv2_workspace_unregister_groups",
     *    methods={"DELETE"}
     * )
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function unregisterGroupsAction(Request $request, Workspace $workspace): JsonResponse
    {
        $query = $request->query->all();
        $groups = $this->om->getRepository(Group::class)->findBy(['uuid' => $query['ids']]);

        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $this->workspaceManager->unregister($group, $workspace);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Register users/groups in different workspaces.",
     *     parameters={
     *         {"name": "role", "type": {"string"}, "description": "The role translation key"},
     *     }
     * )
     *
     * @Route("/register/{role}", name="apiv2_workspace_register", methods={"PATCH"}, defaults={"role":""}, requirements={"role": ".+"})
     *
     * @throws InvalidDataException
     */
    public function registerAction(string $role, Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);

        $workspaces = isset($data['workspaces']) ? $this->om->getRepository(Workspace::class)->findBy(['uuid' => $data['workspaces']]) : [];
        $users = isset($data['users']) ? $this->om->getRepository(User::class)->findBy(['uuid' => $data['users']]) : [];
        $groups = isset($data['groups']) ? $this->om->getRepository(Group::class)->findBy(['uuid' => $data['groups']]) : [];

        foreach ($workspaces as $workspace) {
            if ('' === $role) {
                $roleEntity = $workspace->getDefaultRole();
            } else {
                $roleEntity = $this->om->getRepository(Role::class)
                    ->findOneBy(['translationKey' => $role, 'workspace' => $workspace]);
            }
            if (!empty($users)) {
                $this->crud->patch($roleEntity, 'user', Crud::COLLECTION_ADD, $users);
            }
            if (!empty($groups)) {
                $this->crud->patch($roleEntity, 'group', Crud::COLLECTION_ADD, $groups);
            }
        }

        return new JsonResponse(array_map(function ($workspace) {
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
     *
     * @Route("/unregister", name="apiv2_workspace_self_unregister", methods={"DELETE"})
     */
    public function selfUnregisterAction(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $workspaces = $this->decodeIdsString($request, Workspace::class, 'workspaces');

        foreach ($workspaces as $workspace) {
            $this->workspaceManager->unregister($user, $workspace);
        }

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @ApiDoc(
     *     description="Self-register to a workspace that allows it.",
     *     parameters={
     *         {"name": "workspace", "type": {"string"}, "description": "The workspace uuid"}
     *     }
     * )
     *
     * @Route(
     *     "/{workspace}/register/self",
     *     name="apiv2_workspace_self_register",
     *     methods={"PUT"}
     * )
     *
     * @EXT\ParamConverter(
     *     "workspace",
     *     class = "Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfRegisterAction(Workspace $workspace, User $currentUser): JsonResponse
    {
        if (!$workspace->getSelfRegistration() || $workspace->isArchived()) {
            throw new AccessDeniedException();
        }

        if (!$this->workspaceManager->isRegistered($workspace, $currentUser)) {
            if (!$workspace->getRegistrationValidation()) {
                $this->workspaceManager->addUser($workspace, $currentUser);
            } elseif (!$this->registrationQueueManager->isUserInValidationQueue($workspace, $currentUser)) {
                $this->registrationQueueManager->addUserQueue($workspace, $currentUser);
            }
        }

        return new JsonResponse(
            $this->serializer->serialize($workspace)
        );
    }
}
