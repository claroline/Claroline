<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Security\Voter;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class BadgeClassVoter extends AbstractVoter
{
    const GRANT = 'GRANT';

    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getClass(): string
    {
        return BadgeClass::class;
    }

    /**
     * @param BadgeClass $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        // give all rights if organization manager
        if (!empty($object->getOrganizations()) && $this->isOrganizationManager($token, $object)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        switch ($attributes[0]) {
            case self::OPEN:
                // has open rights on the tool
                if ($this->isToolGranted(self::OPEN, 'badges', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::CREATE:
            case self::EDIT:
            case self::PATCH:
            case self::DELETE:
                // has edit rights on the tool
                if ($this->isToolGranted(self::EDIT, 'badges', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::ADMINISTRATE:
                // has edit rights on the tool
                if ($this->isToolGranted(self::ADMINISTRATE, 'badges', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::GRANT:
                // has grant rights on the tool
                if ($this->isToolGranted(self::GRANT, 'badges', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                // the badge is configured to allow users which own the badge to grant it
                // and current user own the badge TODO
                if ($token->getUser() instanceof User && $object->hasIssuingPeer()) {
                    $assertion = $this->om->getRepository(Assertion::class)->findOneBy(['badge' => $object, 'recipient' => $token->getUser()]);
                    if ($assertion) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::ADMINISTRATE, self::EDIT, self::DELETE, self::PATCH, self::GRANT];
    }
}
