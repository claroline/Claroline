<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Functional;

use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class RemoveBadgeEvent extends Event
{
    private $user;
    private $badge;

    public function __construct(User $user, BadgeClass $badge)
    {
        $this->user = $user;
        $this->badge = $badge;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRole(): BadgeClass
    {
        return $this->badge;
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans('removeBadge', ['username' => $this->user->getUsername(), 'badge' => $this->badge->getName()], 'functional');
    }
}
