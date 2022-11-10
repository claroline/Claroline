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
    /**
     * The auto increment ID of the workspace to copy.
     *
     * @var int
     */
    private $workspaceId;

    /**
     * The options to pass to the Crud::copy.
     *
     * @var array
     */
    private $options;

    public function __construct(int $workspaceId, ?array $options = [])
    {
        $this->workspaceId = $workspaceId;
        $this->options = $options;
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
