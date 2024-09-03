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
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Manager\MailManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/user", name="apiv2_user_")
 */
class UserController extends AbstractCrudController
{
    use PermissionCheckerTrait;
    use HasRolesTrait;
    use HasOrganizationsTrait;
    use HasGroupsTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly UserManager $manager,
        private readonly MailManager $mailManager,
        private readonly ToolManager $toolManager,
        private readonly WorkspaceManager $workspaceManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'user';
    }

    public static function getClass(): string
    {
        return User::class;
    }

    /**
     * @ApiDoc(
     *     description="Create the personal workspaces of an array of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     *
     * @Route("/pws", name="pws_create", methods={"POST"})
     */
    public function createPersonalWorkspaceAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if (!$user->getPersonalWorkspace() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->workspaceManager->createPersonalWorkspace($user);
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
     *
     * @Route("/pws", name="pws_delete", methods={"DELETE"})
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
     * @ApiDoc(
     *     description="Enable a list of users.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     *
     * @Route("/enable", name="enable", methods={"PUT"})
     */
    public function enableAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if (!$user->isEnabled() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->manager->enable($user);
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
     *
     * @Route("/disable", name="disable", methods={"PUT"})
     */
    public function disableAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if ($user->isEnabled() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->manager->disable($user);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    /**
     * @Route("/disable_inactive", name="disable_inactive", methods={"PUT"})
     */
    public function disableInactiveAction(Request $request): JsonResponse
    {
        $tool = $this->toolManager->getOrderedTool('community', DesktopContext::getName());
        $this->checkPermission('ADMINISTRATE', $tool, [], true);

        $data = $this->decodeRequest($request);
        if (empty($data['lastActivity'])) {
            throw new InvalidDataException('Last login date is required');
        }

        $this->manager->disableInactive(DateNormalizer::denormalize($data['lastActivity']));

        return new JsonResponse();
    }

    /**
     * @ApiDoc(
     *     description="Reset a list of user password.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     *
     * @Route("/password/reset", name="password_reset", methods={"PUT"})
     */
    public function resetPasswordAction(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->decodeIdsString($request, User::class);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if ($this->checkPermission('ADMINISTRATE', $user)) {
                $this->mailManager->sendInitPassword($user);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'deleteBulk' => [Options::SOFT_DELETE],
            'create' => [
                // maybe move these options in another class
                Options::WORKSPACE_VALIDATE_ROLES,
                Options::SERIALIZE_FACET,
            ],
            'get' => [Options::SERIALIZE_FACET],
            'update' => [Options::SERIALIZE_FACET],
        ]);
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();

            if ($user instanceof User) {
                // only shows users of the same organizations
                return [
                    'organizations' => array_map(function (Organization $organization) {
                        return $organization->getUuid();
                    }, $user->getOrganizations()),
                ];
            }

            return [
                'organizations' => [],
            ];
        }

        return [];
    }

    /**
     * @Route("/request-deletion", name="request_account_deletion", methods={"POST"})
     *
     * @todo : to move in privacy plugin when available.
     */
    public function requestAccountDeletionAction(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $this->mailManager->sendRequestToDPO($user);

        return new JsonResponse(null, 204);
    }
}
