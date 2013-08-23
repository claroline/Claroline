<?php

namespace Claroline\CoreBundle\Library\Security;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Claroline\CoreBundle\Library\Security\Token\ViewAsToken;

/**
 * @DI\Service("claroline.security.token_updater")
 */
class TokenUpdater
{
    private $sc;
    private $om;

    /**
     * @DI\InjectParams({
     *     "context" = @DI\Inject("security.context"),
     *     "om"      = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param SecurityContextInterface $context
     */
    public function __construct($context, $om)
    {
        $this->sc = $context;
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

    public function cancelUsurpation($token)
    {
        $user = $token->getUser();
        $this->om->refresh($user);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->sc->setToken($token);
    }

    private function updateNormal($token)
    {
        $user = $token->getUser();
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->sc->setToken($token);
    }
}
