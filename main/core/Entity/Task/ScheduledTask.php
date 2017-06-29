<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Task;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Task\ScheduledTaskRepository")
 * @ORM\Table(name="claro_scheduled_task")
 */
class ScheduledTask
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\Column(name="task_type")
     * @Assert\NotBlank()
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("type")
     */
    protected $type;

    /**
     * @ORM\Column(name="task_name", nullable=true)
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("name")
     */
    protected $name;

    /**
     * @ORM\Column(name="scheduled_date", type="datetime")
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("scheduledDate")
     */
    protected $scheduledDate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("executed")
     */
    protected $executed = false;

    /**
     * @ORM\Column(name="execution_date", type="datetime", nullable=true)
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("executionDate")
     */
    protected $executionDate;

    /**
     * @ORM\Column(name="execution_status", nullable=true)
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("executionStatus")
     */
    protected $executionStatus;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_scheduled_task_users")
     * @Groups({"api_user_min"})
     */
    protected $users;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", nullable=true, onDelete="SET NULL")
     * @Groups({"api_user_min"})
     * @SerializedName("group")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     * @Groups({"api_user_min"})
     */
    protected $workspace;

    /**
     * @ORM\Column(name="task_data", type="json_array", nullable=true)
     * @Groups({"api_task", "api_user_min"})
     * @SerializedName("data")
     */
    protected $data;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getScheduledDate()
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(\DateTime $scheduledDate)
    {
        $this->scheduledDate = $scheduledDate;
    }

    public function isExecuted()
    {
        return $this->executed;
    }

    public function setExecuted($executed)
    {
        $this->executed = $executed;
    }

    public function getExecutionDate()
    {
        return $this->executionDate;
    }

    public function setExecutionDate(\DateTime $executionDate = null)
    {
        $this->executionDate = $executionDate;
    }

    public function getExecutionStatus()
    {
        return $this->executionStatus;
    }

    public function setExecutionStatus($executionStatus)
    {
        $this->executionStatus = $executionStatus;
    }

    public function getUsers()
    {
        return $this->users->toArray();
    }

    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    public function emptyUsers()
    {
        $this->users->clear();
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(Group $group = null)
    {
        $this->group = $group;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public static function getSearchableFields()
    {
        return ['type', 'name'];
    }
}
