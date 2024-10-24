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

use Claroline\AppBundle\Messenger\Message\AsyncLowMessageInterface;

/**
 * Copy a workspace.
 */
class CopyWorkspace implements AsyncLowMessageInterface
{
    public function __construct(
        private readonly int $workspaceId,
        private readonly ?array $options = []
    ) {
    }

    public function getWorkspaceId(): int
    {
        return $this->workspaceId;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }
}
