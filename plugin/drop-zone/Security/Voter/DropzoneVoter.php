<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Security\Voter;

use Claroline\CoreBundle\Security\AbstractVoter;
use Claroline\DropZoneBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class DropzoneVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ($object instanceof Dropzone) {
            return $this->checkPermission($token, $object->getResourceNode(), $attributes, $options);
        }
    }

    public function getClass()
    {
        return 'Claroline\DropZoneBundle\Entity\Dropzone';
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
