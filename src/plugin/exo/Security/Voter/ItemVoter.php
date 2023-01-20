<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Manager\Item\ShareManager;

class ItemVoter extends AbstractVoter
{
    /** @var ShareManager */
    private $shareManager;

    public function __construct(
        ShareManager $shareManager
    ) {
        $this->shareManager = $shareManager;
    }

    public function getClass(): string
    {
        return Item::class;
    }

    /**
     * @param Item $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if (!$token->getUser() instanceof User) {
            return VoterInterface::ACCESS_DENIED;
        }

        if ($object->getCreator() && $object->getCreator()->getId() === $token->getUser()->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->shareManager->canEdit($object, $token->getUser())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getSupportedActions(): array
    {
        return [self::EDIT, self::DELETE];
    }
}
