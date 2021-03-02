<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Security;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForgotPasswordEvent extends Event
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getMessage(TranslatorInterface $translator)
    {
        return $translator->trans('forgotPassword', ['username' => $this->user->getUsername()], 'security');
    }
}
