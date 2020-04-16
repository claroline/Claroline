<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class TokenUpdater
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;

    /**
     * TokenUpdater constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectManager         $om
     */
    public function __construct(TokenStorageInterface $tokenStorage, ObjectManager $om)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
    }

    public function update(TokenInterface $token)
    {
        $usurper = false;

        $roles = $token->getRoles();
        foreach ($roles as $role) {
            if ('ROLE_PREVIOUS_ADMIN' === $role->getRole()) {
                return;
            }

            //May be better to check the class of the token.
            if ('ROLE_USURPATE_WORKSPACE_ROLE' === $role->getRole()) {
                $usurper = true;
            }
        }

        if ($usurper) {
            $this->updateUsurper($token);
        } else {
            $this->updateNormal($token);
        }
    }

    private function updateUsurper(TokenInterface $token)
    {
        // no implementation yet
    }

    public function cancelUserUsurpation(TokenInterface $token)
    {
        $roles = $token->getRoles();

        foreach ($roles as $role) {
            if ($role instanceof SwitchUserRole) {
                $user = $role->getSource()->getUser();
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->tokenStorage->setToken($token);

                return;
            }
        }
    }

    public function cancelUsurpation(TokenInterface $token)
    {
        $user = $token->getUser();
        $this->om->refresh($user);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    public function updateNormal(TokenInterface $token)
    {
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof User) {
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->tokenStorage->setToken($token);
            }
        }
    }
}
