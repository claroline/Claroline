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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceUserQueueManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/workspace')]
class RegistrationController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ToolManager $toolManager,
        private readonly WorkspaceManager $workspaceManager,
        private readonly WorkspaceUserQueueManager $registrationQueueManager,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Lists the users who can be registered to the workspace.
     */
    #[Route(path: '/{id}/user/registerable', name: 'apiv2_workspace_list_registerable', methods: ['GET'])]
    public function listRegisterableAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        $results = $this->crud->search(User::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $results->toResponse();
    }

    #[Route(path: '/{id}/user/pending', name: 'apiv2_workspace_list_pending', methods: ['GET'])]
    public function listPendingAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        Request $request
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        return new JsonResponse($this->crud->list(
            WorkspaceRegistrationQueue::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    #[Route(path: '/{id}/registration/validate', name: 'apiv2_workspace_registration_validate', methods: ['PATCH'])]
    public function validatePendingAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        Request $request
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        $query = $request->query->all();
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $query['ids']]);

        foreach ($users as $user) {
            /** @var WorkspaceRegistrationQueue $pending */
            $pending = $this->om->getRepository(WorkspaceRegistrationQueue::class)->findOneBy([
                'user' => $user,
                'workspace' => $workspace,
            ]);

            $this->registrationQueueManager->validateRegistration($pending);
            $this->registrationQueueManager->removeRegistration($pending);
        }

        return new JsonResponse($this->crud->list(
            WorkspaceRegistrationQueue::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    #[Route(path: '/{id}/registration/remove', name: 'apiv2_workspace_registration_remove', methods: ['DELETE'])]
    public function removePendingAction(
        Request $request,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        $query = $request->query->all();
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $query['ids']]);

        foreach ($users as $user) {
            /** @var WorkspaceRegistrationQueue $pending */
            $pending = $this->om->getRepository(WorkspaceRegistrationQueue::class)->findOneBy([
                'user' => $user,
                'workspace' => $workspace,
            ]);

            $this->registrationQueueManager->removeRegistration($pending);
        }

        return new JsonResponse($this->crud->list(
            WorkspaceRegistrationQueue::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    #[Route(path: '/{id}/users/unregister', name: 'apiv2_workspace_unregister_users', methods: ['DELETE'])]
    public function unregisterUsersAction(
        Request $request,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);

        $this->workspaceManager->unregisterUsers($users, $workspace);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{id}/groups/unregister', name: 'apiv2_workspace_unregister_groups', methods: ['DELETE'])]
    public function unregisterGroupsAction(
        Request $request,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace
    ): JsonResponse {
        $this->checkToolAccess('FOLLOW', $workspace->getUuid());

        $ids = $this->decodeRequest($request);
        $groups = $this->om->getRepository(Group::class)->findBy(['uuid' => $ids]);

        $this->workspaceManager->unregisterGroups($groups, $workspace);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/register/{role}', name: 'apiv2_workspace_register', requirements: ['role' => '.+'], defaults: ['role' => ''], methods: ['PATCH'])]
    public function registerAction(string $role, Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);

        $workspaces = !empty($data['workspaces']) ? $this->om->getRepository(Workspace::class)->findBy(['uuid' => $data['workspaces']]) : [];
        $users = !empty($data['users']) ? $this->om->getRepository(User::class)->findBy(['uuid' => $data['users']]) : [];
        $groups = !empty($data['groups']) ? $this->om->getRepository(Group::class)->findBy(['uuid' => $data['groups']]) : [];

        foreach ($workspaces as $workspace) {
            $roleEntity = null;
            if (!empty($role)) {
                $roleEntity = $this->om->getRepository(Role::class)
                    ->findOneBy(['translationKey' => $role, 'workspace' => $workspace]);
            }

            if (!empty($users)) {
                $this->workspaceManager->registerUsers($users, $workspace, $roleEntity);
            }
            if (!empty($groups)) {
                $this->workspaceManager->registerGroups($groups, $workspace, $roleEntity);
            }
        }

        return new JsonResponse(array_map(function ($workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    #[Route(path: '/unregister', name: 'apiv2_workspace_self_unregister', methods: ['DELETE'])]
    public function selfUnregisterAction(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();

        $ids = $this->decodeRequest($request);
        $workspaces = $this->om->getRepository(Workspace::class)->findBy(['uuid' => $ids]);

        foreach ($workspaces as $workspace) {
            $this->workspaceManager->unregisterUsers([$user], $workspace);
        }

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    #[Route(path: '/{workspace}/register/self', name: 'apiv2_workspace_self_register', methods: ['PUT'])]
    public function selfRegisterAction(
        #[MapEntity(mapping: ['workspace' => 'uuid'])]
        Workspace $workspace,
        #[CurrentUser] ?User $currentUser
    ): JsonResponse {
        if (null === $currentUser || !$workspace->getSelfRegistration() || $workspace->isArchived()) {
            throw new AccessDeniedException();
        }

        if (!$this->workspaceManager->isRegistered($workspace, $currentUser)) {
            if (!$workspace->getRegistrationValidation()) {
                $this->workspaceManager->registerUsers([$currentUser], $workspace);
            } elseif (!$this->registrationQueueManager->isUserInValidationQueue($workspace, $currentUser)) {
                $this->registrationQueueManager->addUserQueue($workspace, $currentUser);
            }
        }

        return new JsonResponse(
            $this->serializer->serialize($workspace)
        );
    }

    private function checkToolAccess(string $permission = 'OPEN', ?string $contextId = null): void
    {
        if ($contextId) {
            $communityTool = $this->toolManager->getOrderedTool('community', WorkspaceContext::getName(), $contextId);
        } else {
            $communityTool = $this->toolManager->getOrderedTool('community', DesktopContext::getName());
        }

        if (is_null($communityTool) || !$this->authorization->isGranted($permission, $communityTool)) {
            throw new AccessDeniedException(sprintf('Operation "%s" cannot be done on object %s', $permission, get_class($communityTool)));
        }
    }
}
