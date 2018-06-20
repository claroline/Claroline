<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Security;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class SubjectVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        //check if we can create the message here
    }

    public function getClass()
    {
        return 'Claroline\ForumBundle\Entity\Message';
    }

    public function getSupportedActions()
    {
        return[self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
