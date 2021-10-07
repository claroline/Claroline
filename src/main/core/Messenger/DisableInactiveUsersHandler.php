<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Messenger\Message\DisableInactiveUsers;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DisableInactiveUsersHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var UserManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        UserManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public function __invoke(DisableInactiveUsers $message)
    {
        $users = $this->om->getRepository(User::class)->findInactiveSince($message->getLastLogin());

        $this->om->startFlushSuite();
        foreach ($users as $user) {
            $this->manager->disable($user);
        }
        $this->om->endFlushSuite();
    }
}
