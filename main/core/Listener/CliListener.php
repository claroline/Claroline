<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CliListener
{
    private $tokenStorage;
    private $userManager;
    private $em;

    /**
     * CliListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager         $em
     * @param UserManager           $userManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManager $em,
        UserManager $userManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->userManager = $userManager;
    }

    /**
     * Sets claroline default admin for cli because it's very annoying otherwise to do it manually everytime.
     *
     * @param ConsoleCommandEvent $event
     */
    public function setDefaultUser(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        if ($command instanceof AdminCliCommand) {
            $user = $this->userManager->getDefaultClarolineAdmin();
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);
        }
    }
}
