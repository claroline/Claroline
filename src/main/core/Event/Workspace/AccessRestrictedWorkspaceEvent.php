<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Workspace;

/**
 * Event dispatched when a workspace is opened but the user cannot access it.
 */
class AccessRestrictedWorkspaceEvent extends AbstractWorkspaceEvent
{
    private ?array $error = null;

    public function getError(): ?array
    {
        return $this->error;
    }

    public function addError(string $code, string $message, ?array $additional = []): void
    {
        $this->error = [
            'code' => $code,
            'message' => $message,
            'additional' => $additional,
        ];
    }
}
