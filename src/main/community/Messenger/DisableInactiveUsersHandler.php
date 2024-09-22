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
use Claroline\CommunityBundle\Messenger\Message\DisableInactiveUsers;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DisableInactiveUsersHandler
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly UserManager $manager
    ) {
    }

    public function __invoke(DisableInactiveUsers $message): void
    {
        $users = $this->om->getRepository(User::class)->findInactiveSince($message->getLastActivity());

        $this->om->startFlushSuite();
        foreach ($users as $user) {
            $this->manager->disable($user);
        }
        $this->om->endFlushSuite();
    }
}
