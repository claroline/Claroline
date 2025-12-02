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

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Manager\MailManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/user', name: 'apiv2_user_')]
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
        private readonly ToolManager $toolManager
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

    #[Route(path: '/{contextId}', name: 'list', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest(),
        ?string $contextId = null
    ): StreamedJsonResponse {
        $this->checkToolAccess('OPEN', $contextId);

        if ($contextId) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);
            $finderRequest->addFilter('workspace', $workspace->getUuid());
        }

        return parent::listAction($finderRequest);
    }

    #[Route(path: '/enable', name: 'enable', methods: ['PUT'])]
    public function enableAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if ($user->isDisabled() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->manager->enable($user);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    #[Route(path: '/disable', name: 'disable', methods: ['PUT'])]
    public function disableAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();

        $processed = [];
        foreach ($users as $user) {
            if (!$user->isDisabled() && $this->checkPermission('ADMINISTRATE', $user)) {
                $this->manager->disable($user);
                $processed[] = $user;
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (User $user) {
            return $this->serializer->serialize($user);
        }, $processed));
    }

    #[Route(path: '/disable_inactive', name: 'disable_inactive', methods: ['PUT'])]
    public function disableInactiveAction(Request $request): JsonResponse
    {
        $this->checkToolAccess('ADMINISTRATE');

        $data = $this->decodeRequest($request);
        if (empty($data['lastActivity'])) {
            throw new InvalidDataException('Last login date is required');
        }

        $this->manager->disableInactive(DateNormalizer::denormalize($data['lastActivity']));

        return new JsonResponse();
    }

    #[Route(path: '/password/reset', name: 'password_reset', methods: ['PUT'])]
    public function resetPasswordAction(Request $request): JsonResponse
    {
        $this->om->startFlushSuite();

        $userIds = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $userIds]);

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
