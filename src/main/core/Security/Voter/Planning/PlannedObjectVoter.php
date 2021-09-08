<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter\Planning;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PlannedObjectVoter extends AbstractVoter
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param PlannedObject $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        // find the custom event object
        $event = $this->om->getRepository($object->getClass())->findOneBy(['uuid' => $object->getUuid()]);

        if ($event) {
            // forward rights check to the custom voter
            if ($this->isGranted($attributes[0], $event)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return AbstractVoter::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return PlannedObject::class;
    }
}
