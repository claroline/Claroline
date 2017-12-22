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

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

/**
 * @DI\Service("claroline.security.token_updater")
 */
class TokenUpdater
{
    private $sc;
    private $om;

    /**
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, $om)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
    }

    public function update(AbstractToken $token)
    {
        $usurpator = false;
        $roles = $token->getRoles();

        foreach ($roles as $role) {
            if ($role->getRole() === 'ROLE_PREVIOUS_ADMIN') {
                return;
            }

            //May be better to check the class of the token.
            if ($role->getRole() === 'ROLE_USURPATE_WORKSPACE_ROLE') {
                $usurpator = true;
            }
        }

        if ($usurpator) {
            $this->updateUsurpator($token);
        } else {
            $this->updateNormal($token);
        }
    }

    private function updateUsurpator($token)
    {
        //no implementation yet
    }

    public function cancelUserUsurpation($token)
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

    public function cancelUsurpation($token)
    {
        $user = $token->getUser();
        $this->om->refresh($user);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    public function updateNormal($token)
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
