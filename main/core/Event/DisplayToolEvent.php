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
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class DisplayToolEvent extends Event implements DataConveyorEventInterface
{
    protected $response;
    protected $content;
    protected $isPopulated = false;

    public function __construct(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
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

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
