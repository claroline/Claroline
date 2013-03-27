<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

class WorkspaceLogEvent extends Event
{
    private $type;
    private $user;
    private $workspace;
    private $data;

    const ACCESS_ACTION = 'workspace_access';

    public function __construct($type, $date, User $user, AbstractWorkspace $workspace, $data = '')
    {
        $this->type = $type;
        $this->date = $date;
        $this->user = $user;
        $this->workspace = $workspace;
        $this->data = $data;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getData()
    {
        return $this->data;
    }
}