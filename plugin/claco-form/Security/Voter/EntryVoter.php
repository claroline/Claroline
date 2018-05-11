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
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class EntryVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::EDIT:
                return $this->checkEdit($token, $object);
        }
    }

    public function getClass()
    {
        return 'Claroline\ClacoFormBundle\Entity\Entry';
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }

    private function checkEdit(TokenInterface $token, Entry $entry)
    {
        $clacoForm = $entry->getClacoForm();
        $user = $token->getUser();

        if ($this->isGranted(self::EDIT, $clacoForm->getResourceNode()) ||
            ($clacoForm->isEditionEnabled() && 'anon.' !== $user && $entry->getUser()->getUuid() === $token->getUser()->getUuid())
        ) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
