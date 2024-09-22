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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Manager\MailManager;
use Claroline\CoreBundle\Controller\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/group', name: 'apiv2_group_')]
class GroupController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasRolesTrait;
    use HasOrganizationsTrait;
    use PermissionCheckerTrait;

    private TokenStorageInterface $tokenStorage;
    private MailManager $mailManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        MailManager $mailManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->mailManager = $mailManager;
    }

    public static function getName(): string
    {
        return 'group';
    }

    public static function getClass(): string
    {
        return Group::class;
    }

    #[Route(path: '/password/reset', name: 'password_reset', methods: ['PUT'])]
    public function resetPasswordAction(Request $request): JsonResponse
    {
        /** @var Group[] $groups */
        $groups = $this->decodeIdsString($request, Group::class);

        $this->om->startFlushSuite();
        $i = 0;
        foreach ($groups as $group) {
            $users = $this->om->getRepository(User::class)->findByGroup($group);
            foreach ($users as $user) {
                if ($this->authorization->isGranted('ADMINISTRATE', $user)) {
                    $this->mailManager->sendInitPassword($user);
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

    protected function getDefaultHiddenFilters(): array
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();

            return [
                'organizations' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations()),
            ];
        }

        return [];
    }
}
