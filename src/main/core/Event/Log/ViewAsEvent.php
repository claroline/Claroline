<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class ViewAsEvent extends Event
{
    private $user;
    private $role;

    public function __construct(User $user, $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return sprintf($translator->trans('viewAs', [], 'security'), $this->user->getUsername(), $this->role);
    }
}
