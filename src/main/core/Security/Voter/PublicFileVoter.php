<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PublicFileVoter extends AbstractVoter
{
    /**
     * @param PublicFile $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        return VoterInterface::ACCESS_GRANTED;
    }

    public function getClass(): string
    {
        return PublicFile::class;
    }
}
