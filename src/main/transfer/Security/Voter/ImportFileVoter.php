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
use Claroline\TransferBundle\Entity\ImportFile;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ImportFileVoter extends AbstractTransferFileVoter
{
    public const IMPORT = 'IMPORT';

    public function getClass(): string
    {
        return ImportFile::class;
    }

    /**
     * @param ImportFile $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::CREATE:
                if ($this->isToolGranted(self::IMPORT, 'import', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::OPEN:
                if ($this->isToolGranted(self::OPEN, 'import', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::EDIT:
            case self::DELETE:
                if ($this->isToolGranted(self::EDIT, 'import', $object->getWorkspace() ?? null)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                if ($object->getCreator() && $token->getUser() instanceof User && $object->getCreator()->getId() === $token->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
