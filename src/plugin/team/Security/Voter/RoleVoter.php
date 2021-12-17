<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Security\Voter;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Repository\TeamRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class RoleVoter extends AbstractVoter
{
    /** @var ObjectManager */
    private $om;

    /** @var TeamRepository */
    private $teamRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->teamRepo = $om->getRepository(Team::class);
    }

    /**
     * @param Team $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $teams = $this->teamRepo->findByRole($object->getName());

        // user can open one of the team linked to the role
        // he should be able to open the linked role
        $open = 0 !== count(array_filter($teams, function (Team $team) {
            return $this->isGranted('OPEN', $team);
        }));

        switch ($attributes[0]) {
            case self::OPEN:
            case self::VIEW:
                if ($open) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                // abstain and let the base RoleVoter decide what to do
                return VoterInterface::ACCESS_ABSTAIN;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return Role::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW];
    }
}
