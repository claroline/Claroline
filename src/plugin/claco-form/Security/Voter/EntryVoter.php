<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Security\Voter;

use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class EntryVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::OPEN:
                return $this->checkOpen($object);
            case self::CREATE:
                return $this->checkCreate($object);
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass(): string
    {
        return Entry::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }

    private function checkOpen(Entry $entry): int
    {
        $clacoForm = $entry->getClacoForm();

        if ($this->isGranted('OPEN', $clacoForm->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkCreate(Entry $entry): int
    {
        $clacoForm = $entry->getClacoForm();

        if ($this->isGranted('ADD-ENTRY', $clacoForm->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(TokenInterface $token, Entry $entry): int
    {
        $clacoForm = $entry->getClacoForm();
        $user = $token->getUser();

        if ($this->isGranted(self::EDIT, $clacoForm->getResourceNode()) ||
            ($clacoForm->isEditionEnabled() && $user instanceof User && $entry->getUser()->getUuid() === $user->getUuid())
        ) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($clacoForm->isEditionEnabled() && $user instanceof User) {
            $entryUsers = $entry->getEntryUsers();

            foreach ($entryUsers as $entryUser) {
                if ($entryUser->isShared() && $entryUser->getUser()->getUuid() === $user->getUuid()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
