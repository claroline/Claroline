<?php

namespace Claroline\CoreBundle\Library\Widget\Event;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetDesktopEvent
{
    private $user;

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

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

}

