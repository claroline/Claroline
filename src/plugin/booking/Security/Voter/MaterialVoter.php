<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BookingBundle\Security\Voter;

use Claroline\BookingBundle\Entity\Material;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MaterialVoter extends AbstractVoter
{
    public function getClass()
    {
        return Material::class;
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        /** @var OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);

        $bookingTool = $orderedToolRepo->findOneByNameAndDesktop('booking');

        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::PATCH:
            case self::DELETE:
                if ($this->isGranted('EDIT', $bookingTool)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::OPEN:
            case self::VIEW:
                if ($this->isGranted('OPEN', $bookingTool)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
