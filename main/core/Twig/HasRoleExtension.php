<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Symfony\Component\Security\Core\Role\SwitchUserRole;

class HasRoleExtension extends \Twig_Extension
{
    private $securityContext;

    public function __construct($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'has_role' => new \Twig_SimpleFunction('has_role', [$this, 'hasRole']),
            'is_impersonated' => new \Twig_SimpleFunction('is_impersonated', [$this, 'isImpersonated']),
        ];
    }

    public function hasRole($role)
    {
        if ($token = $this->tokenStorage->getToken()) {
            foreach ($token->getRoles() as $tokenRole) {
                if ($tokenRole->getRole() === $role) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isImpersonated()
    {
        if ($token = $this->tokenStorage->getToken()) {
            foreach ($token->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'has_role_extension';
    }
}
