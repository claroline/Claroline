<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Security\Voter;

use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Entity\ExportFile;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ExportFileVoter extends AbstractTransferFileVoter
{
    const EXPORT = 'EXPORT';
    const REFRESH = 'REFRESH';

    public function getClass()
    {
        return ExportFile::class;
    }

    /**
     * @param ExportFile $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
                if ($this->isToolGranted(self::EXPORT, 'transfer', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::OPEN:
            case self::VIEW:
                if ($this->isToolGranted(self::OPEN, 'transfer', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::REFRESH:
                if ($this->isToolGranted(self::REFRESH, 'transfer', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                if ($object->getCreator() && $token->getUser() instanceof User && $object->getCreator()->getId() === $token->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT:
            case self::DELETE:
                if ($this->isToolGranted(self::EDIT, 'transfer', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                if ($object->getCreator() && $token->getUser() instanceof User && $object->getCreator()->getId() === $token->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getSupportedActions()
    {
        return array_merge(parent::getSupportedActions(), [self::REFRESH]);
    }
}
