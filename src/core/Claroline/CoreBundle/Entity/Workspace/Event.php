<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_event")
 */
class Event
{
    

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\Column(name="title", type="string" , length=50)
     */
    private $title;

    /**
     *
     * @ORM\Column(name="start", type="integer")
     */
    private $start;

    /**
     *
     * @ORM\Column(name="end", type="integer" , nullable=true)
     */
    private $end;

    /**
     *
     * @ORM\Column(name="description", type="string" , nullable=true)
     */
    private $description;

    /**
     *
     *  @ORM\Column(name="workspace_id", type="integer")
     */
    private $workspaceId;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace", inversedBy="events", cascade={"persist"})
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    private $workspace;

    /**
     *
     *  @ORM\Column(name="user_id", type="integer")
     */
    private $userId;
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getStart()
    {

        return $this->start;
    }

    public function setStart(\DateTime $start)
    {
        $timestamp = $start->getTimestamp();
        $this->start = $timestamp;
    }

    public function getEnd()
    {
        if (is_null($this->end)) {
            return $this->end;
        } else {
            return $this->end;
        }
    }

    public function setEnd(\DateTime $end)
    {
        if (is_null($end)) {
            $this->end = $end;
        }
        $timestamp = $end->getTimestamp();
        $this->end = $timestamp;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getWorkspaceId()
    {
        return $this->workspaceId;
    }

    public function setWorkspaceId($workspaceId)
    {
        $this->workspaceId = $workspaceId;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
    
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;
    }
    
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}