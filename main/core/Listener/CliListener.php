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
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @DI\Service
 */
class CliListener
{
    private $tokenStorage;
    private $userManager;
    private $em;

    /**
     * CliListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "em"            = @DI\Inject("doctrine.orm.entity_manager"),
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager")
     * })
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
     * @DI\Observe("console.command", priority = 17)
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
