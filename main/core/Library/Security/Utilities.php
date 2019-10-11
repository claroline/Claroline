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

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Utilities
{
    /** @var EntityManager */
    private $em;
    /** @var array */
    private $expectedKeysForResource;
    /** @var array */
    private $expectedKeysForWorkspace;

    /**
     * Constructor.
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->expectedTypeOfRight = ['workspace', 'resource'];
        $this->expectedKeysForResource = [
            'open',
            'delete',
            'edit',
            'copy',
            'create',
            'export',
        ];
        $this->expectedKeysForWorkspace = ['canView', 'canDelete', 'canEdit'];
    }

    /**
     * Returns the roles (an array of string) of the $token.
     *
     * @todo remove this $method
     *
     * @param TokenInterface $token
     *
     * @return array
     */
    public function getRoles(TokenInterface $token)
    {
        $roles = [];

        foreach ($token->getRoles() as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }
}
