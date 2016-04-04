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

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class HasRoleExtension extends \Twig_Extension
{
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "tokenStorage"    = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'has_role' => new \Twig_Function_Method($this, 'hasRole'),
            'is_impersonated' => new \Twig_Function_Method($this, 'isImpersonated'),
        );
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
                if ($role instanceof \Symfony\Component\Security\Core\Role\SwitchUserRole) {
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
