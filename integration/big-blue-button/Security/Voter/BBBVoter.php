<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Security\Voter;

use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BBBVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        return $this->isGranted($attributes, $object->getResourceNode());
    }

    public function getClass()
    {
        return BBB::class;
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
