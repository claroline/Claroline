<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SchedulerBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class ExecuteScheduledTask implements AsyncMessageInterface
{
    private $scheduledTaskId;

    public function __construct(int $scheduledTaskId)
    {
        $this->scheduledTaskId = $scheduledTaskId;
    }

    public function getTaskId(): int
    {
        return $this->scheduledTaskId;
    }
}
