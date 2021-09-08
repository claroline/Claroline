<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

abstract class AbstractEventVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function checkEdit(TokenInterface $token, AbstractPlanned $object)
    {
        $workspace = $object->getWorkspace();

        $currentUser = $token->getUser();
        $user = $object->getCreator();

        // the user is the creator of the event
        if ($currentUser instanceof User && (!$user || $currentUser->getUuid() === $user->getUuid())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // the user has EDIT right on the corresponding tool
        /** @var OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->getObjectManager()->getRepository(OrderedTool::class);

        if (!empty($workspace)) {
            $agendaTool = $orderedToolRepo->findOneByNameAndWorkspace('agenda', $workspace);
        } else {
            $agendaTool = $orderedToolRepo->findOneByNameAndDesktop('agenda');
        }

        if ($this->isGranted('EDIT', $agendaTool)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::DELETE];
    }
}
