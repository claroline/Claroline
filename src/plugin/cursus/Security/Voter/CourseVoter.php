<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\CursusBundle\Entity\Course;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CourseVoter extends AbstractVoter
{
    const SELF_REGISTER = 'SELF_REGISTER';

    public function getClass()
    {
        return Course::class;
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        /** @var OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);

        $trainingsTool = $orderedToolRepo->findOneByNameAndDesktop('trainings');
        $toolEdit = $this->isGranted('EDIT', $trainingsTool);

        switch ($attributes[0]) {
            case self::CREATE: // EDIT right on tool
                if ($toolEdit) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT: // admin of organization | EDIT right on tool
            case self::PATCH:
            case self::DELETE:
                if ($toolEdit || $this->isOrganizationManager($token, $object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::OPEN: // member of organization & OPEN right on tool
            case self::VIEW:
                if ($this->isGranted('OPEN', $trainingsTool) && $this->isOrganizationMember($token, $object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::SELF_REGISTER:
                if ($this->isGranted('SELF_REGISTER', $trainingsTool) && $this->isOrganizationMember($token, $object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
