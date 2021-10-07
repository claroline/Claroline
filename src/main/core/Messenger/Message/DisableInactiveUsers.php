<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger\Message;

/**
 * Disable all users which have not logged in since the selected date.
 */
class DisableInactiveUsers
{
    /** @var \DateTimeInterface */
    private $lastLogin;

    public function __construct(\DateTimeInterface $lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    public function getLastLogin(): \DateTimeInterface
    {
        return $this->lastLogin;
    }
}
