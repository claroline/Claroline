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

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

/**
 * Disable all users which have not logged in since the selected date.
 */
class DisableInactiveUsers implements AsyncMessageInterface
{
    /** @var \DateTimeInterface */
    private $lastActivity;

    public function __construct(\DateTimeInterface $lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }

    public function getLastActivity(): \DateTimeInterface
    {
        return $this->lastActivity;
    }
}
