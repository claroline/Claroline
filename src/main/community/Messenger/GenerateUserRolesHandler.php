<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CommunityBundle\Messenger\Message\GenerateUserRoles;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsMessageHandler]
class GenerateUserRolesHandler
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly ObjectManager $om,
        private readonly RoleManager $roleManager,
    ) {
    }

    public function __invoke(GenerateUserRoles $message): void
    {
        $users = $this->om->getRepository(User::class)->findWithNoUserRole(100);

        $this->om->startFlushSuite();
        foreach ($users as $user) {
            $this->roleManager->createUserRole($user);
        }
        $this->om->endFlushSuite();

        $rest = $this->om->getRepository(User::class)->countWithNoUserRole();
        if (0 < $rest) {
            $this->messageBus->dispatch(
                new GenerateUserRoles(),
                [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
            );
        }
    }
}
