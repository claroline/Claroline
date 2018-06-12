<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\PlannedNotificationBundle\Repository\PlannedNotificationRepository")
 * @ORM\Table(name="claro_plannednotificationbundle_planned_notification")
 */
class PlannedNotification
{
    const TYPE_WORKSPACE_USER_REGISTRATION = 'workspace-role-subscribe_user';
    const TYPE_WORKSPACE_GROUP_REGISTRATION = 'workspace-role-subscribe_group';
    const TYPE_WORKSPACE_FIRST_CONNECTION = 'workspace-enter';

    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=false, onDelete="CASCADE")
     *
     * @var Workspace
     */
    protected $workspace;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinTable(name="claro_plannednotificationbundle_planned_notification_role")
     *
     * @var ArrayCollection|Role[]
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\PlannedNotificationBundle\Entity\Message"
     * )
     * @ORM\JoinColumn(name="message_id", nullable=false, onDelete="CASCADE")
     *
     * @var Message
     */
    protected $message;

    /**
     * @ORM\Column(name="triggering_action", nullable=false)
     *
     * @var string
     */
    protected $action;

    /**
     * @ORM\Column(name="planned_interval", type="integer", nullable=false)
     *
     * @var int
     */
    protected $interval = 1;

    /**
     * @ORM\Column(name="by_mail", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $byMail = true;

    /**
     * @ORM\Column(name="by_message", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $byMessage = false;

    /**
     * PlannedNotification constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->roles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param Workspace $workspace
     */
    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    public function emptyRoles()
    {
        $this->roles->clear();
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Message $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return bool
     */
    public function isByMail()
    {
        return $this->byMail;
    }

    /**
     * @param bool $byMail
     */
    public function setByMail($byMail)
    {
        $this->byMail = $byMail;
    }

    /**
     * @return bool
     */
    public function isByMessage()
    {
        return $this->byMessage;
    }

    /**
     * @param bool $byMessage
     */
    public function setByMessage($byMessage)
    {
        $this->byMessage = $byMessage;
    }
}
