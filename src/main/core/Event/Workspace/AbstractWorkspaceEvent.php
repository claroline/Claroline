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
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractWorkspaceEvent extends Event
{
    /** @var Workspace */
    private $workspace;

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
