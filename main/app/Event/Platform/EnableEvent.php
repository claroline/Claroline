<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event\Platform;

use Symfony\Component\EventDispatcher\Event;

class EnableEvent extends Event
{
    /** @var bool */
    private $canceled = false;

    /** @var string */
    private $cancellationMessage = null;

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    /**
     * @return string|null
     */
    public function getCancellationMessage()
    {
        return $this->cancellationMessage;
    }

    /**
     * @param string $message
     */
    public function cancel(string $message = null)
    {
        $this->canceled = true;
        $this->cancellationMessage = $message;
    }
}
