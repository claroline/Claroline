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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a workspace is opened.
 */
class OpenWorkspaceEvent extends Event
{
    /** @var Workspace */
    private $workspace;

    /**
     * OpenWorkspaceEvent constructor.
     *
     * @param Workspace $workspace
     */
    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Gets the loaded workspace Entity.
     *
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }
}
