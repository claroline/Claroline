<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\User;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetDesktopEvent extends Event implements DataConveyorEventInterface
{
    private $user;
    private $content;
    private $isPopulated = false;

    /**
     * Constructor.
     *
     * @param AbstractWorkspace $workspace
     */
    public function __construct($user, $isDefault = false)
    {
        $this->user = $user;
        $this->isDefault = $isDefault;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
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

    public function isDefault()
    {
        return $this->isDefault;
    }

    public function isPopulated() {
        return $this->isPopulated;
    }
}

