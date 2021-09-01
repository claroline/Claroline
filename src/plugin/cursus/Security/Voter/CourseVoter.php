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

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\CursusBundle\Entity\Course;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CourseVoter extends AbstractVoter
{
    const REGISTER = 'REGISTER';

    public function getClass()
    {
        return Course::class;
    }

    /**
     * @param Course $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        /** @var OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);

        $trainingsTool = $orderedToolRepo->findOneByNameAndDesktop('trainings');

        switch ($attributes[0]) {
            case self::CREATE: // EDIT right on tool
                if ($this->isGranted('EDIT', $trainingsTool)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT: // admin of organization | EDIT right on tool
            case self::PATCH:
            case self::DELETE:
                if ($this->isGranted('EDIT', $trainingsTool) || $this->isOrganizationManager($token, $object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::OPEN: // member of organization & OPEN right on tool
            case self::VIEW:
                if ($this->isGranted('OPEN', $trainingsTool) && $this->checkOrganization($token, $object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::REGISTER:
                if ($this->isGranted('REGISTER', $trainingsTool) || $this->isOrganizationManager($token, $object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function checkOrganization(TokenInterface $token, Course $object)
    {
        $currentUser = $token->getUser();

        if ($currentUser instanceof User) {
            $sameOrganization = $this->isOrganizationMember($token, $object);
        } else {
            // show things from default organizations to anonymous
            $sameOrganization = !empty(array_filter($object->getOrganizations()->toArray(), function (Organization $organization) {
                return $organization->isDefault();
            }));
        }

        return $sameOrganization;
    }
}
