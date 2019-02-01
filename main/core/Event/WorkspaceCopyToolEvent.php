<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\EventDispatcher\Event;

class WorkspaceCopyToolEvent extends Event
{
    public function __construct(Workspace $oldWorkspace, Workspace $newWorkspace)
    {
        $this->oldWorkspace = $oldWorkspace;
        $this->newWorkspace = $newWorkspace;
    }

    public function getOldWorkspace()
    {
        return $this->oldWorkspace;
    }

    public function getNewWorkspace()
    {
        return $this->newWorkspace;
    }
}
