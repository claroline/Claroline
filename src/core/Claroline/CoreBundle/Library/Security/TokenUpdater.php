<?php

namespace Claroline\CoreBundle\Library\Security;

use Claroline\CoreBundle\Library\Security\Token\ViewAsToken;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @DI\Service("claroline.security.token_updater")
 */
class TokenUpdater
{
    private $sc;

    /**
     * @DI\InjectParams({
     *     "context" = @DI\Inject("security.context")
     * })
     *
     * @param SecurityContextInterface $context
     */
    public function __construct($context)
    {
        $this->sc = $context;
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

    private function updateUsurpator()
    {
        throw new \Exception('No implementation for updateUsurpator yet.');
    }

    private function updateNormal($token)
    {
        $user = $token->getUser();
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->sc->setToken($token);
    }
}
