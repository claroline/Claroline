<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetDesktopEvent extends Event
{
    private $user;
    private $content;

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

}

