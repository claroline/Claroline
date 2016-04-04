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

class ImportWidgetConfigEvent extends Event
{
    private $config;
    private $workspace;

    public function __construct(array $config, Workspace $workspace)
    {
        $this->config = $config;
        $this->workspace = $workspace;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getArchive()
    {
        return $this->archive;
    }
}
