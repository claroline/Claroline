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

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class ConfigureWorkspaceToolEvent extends Event implements DataConveyorEventInterface
{
    private $content;
    private $tool;
    private $workspace;
    private $isPopulated = false;

    /**
     * Constructor.
     */
    public function __construct(Tool $tool, Workspace $workspace)
    {
        $this->tool = $tool;
        $this->workspace = $workspace;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setContent($content)
    {
        $this->isPopulated = true;
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
