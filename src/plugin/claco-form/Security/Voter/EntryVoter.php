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

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class EntryVoter extends AbstractVoter
{
    public function __construct(
        private readonly ClacoFormManager $clacoFormManager,
    ) {
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $clacoForm = $object->getClacoForm();
        if ($this->isGranted('EDIT', $clacoForm->getResourceNode())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        switch ($attributes[0]) {
            case self::OPEN:
                return $this->checkOpen($token, $object);
            case self::CREATE:
                return $this->checkCreate($token, $object);
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($token, $object);
            case self::ADMINISTRATE:
                return $this->checkAdministrate($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass(): string
    {
        return Entry::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE];
    }

    private function checkOpen(TokenInterface $token, Entry $entry): int
    {
        $clacoForm = $entry->getClacoForm();

        if ($this->isGranted('OPEN', $clacoForm->getResourceNode())) {
            $clacoForm = $entry->getClacoForm();
            /** @var User|string $user */
            $user = $token->getUser();

            if (($user && $entry->getUser() === $user)
                || $this->isEntryManager($entry, $user)
                || ((Entry::PUBLISHED === $entry->getStatus()) && $clacoForm->getSearchEnabled())
            ) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkAdministrate(TokenInterface $token, Entry $entry): int
    {
        if ($this->isEntryManager($entry, $token->getUser())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkCreate(TokenInterface $token, Entry $entry): int
    {
        $clacoForm = $entry->getClacoForm();

        if ($this->isGranted('CONTRIBUTE', $clacoForm->getResourceNode())) {
            if ($clacoForm->getMaxEntries()
                && $this->clacoFormManager->countUserEntries($clacoForm, $token->getUser()) >= $clacoForm->getMaxEntries()) {
                return VoterInterface::ACCESS_DENIED;
            }

            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(TokenInterface $token, Entry $entry): int
    {
        $clacoForm = $entry->getClacoForm();
        $user = $token->getUser();

        if (!$entry->isLocked() && ($this->isEntryManager($entry, $user)
            || ($clacoForm->isEditionEnabled() && $user instanceof User && $entry->getUser()->getUuid() === $user->getUuid()))
        ) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function isEntryManager(Entry $entry, ?User $user): bool
    {
        if ($user) {
            $categories = $entry->getCategories();
            foreach ($categories as $category) {
                $managers = $category->getManagers();

                foreach ($managers as $manager) {
                    if ($manager->getId() === $user->getId()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
        }

        return false;
    }
}
