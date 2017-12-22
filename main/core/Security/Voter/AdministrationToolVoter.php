<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class AdministrationToolVoter implements VoterInterface
{
    /**
     * @DI\InjectParams({"em" = @DI\Inject("doctrine.orm.entity_manager")})
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof AdminTool) {
            $roles = $object->getRoles();
            $tokenRoles = $token->getRoles();

            foreach ($tokenRoles as $tokenRole) {
                foreach ($roles as $role) {
                    if ($role->getRole() === $tokenRole->getRole()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }
}
