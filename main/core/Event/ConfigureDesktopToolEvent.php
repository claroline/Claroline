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

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Symfony\Component\EventDispatcher\Event;

class ConfigureDesktopToolEvent extends Event implements DataConveyorEventInterface
{
    private $content;
    private $tool;
    private $isPopulated = false;

    /**
     * Constructor.
     */
    public function __construct(Tool $tool)
    {
        $this->tool = $tool;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setContent($content)
    {
        $this->content = $content;
        $this->isPopulated = true;
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
