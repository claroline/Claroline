<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SchedulerBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\SchedulerBundle\Repository\ScheduledTaskRepository")
 * @ORM\Table(name="claro_scheduled_task")
 */
class ScheduledTask
{
    use Id;
    use Name;
    use Uuid;

    /**
     * The task is waiting for execution.
     */
    const PENDING = 'pending';

    /**
     * The task is currently executed.
     */
    const IN_PROGRESS = 'in_progress';

    /**
     * The task has been executed without errors.
     */
    const SUCCESS = 'success';

    /**
     * The task has failed due to some errors.
     */
    const ERROR = 'error';

    /**
     * @ORM\Column(name="task_type")
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(name="scheduled_date", type="datetime")
     *
     * @var \DateTime
     */
    private $scheduledDate;

    /**
     * @ORM\Column(name="execution_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $executionDate = null;

    /**
     * @ORM\Column(name="execution_status", nullable=true)
     *
     * @var string
     */
    private $status = self::PENDING;

    /**
     * @ORM\Column(name="task_data", type="json", nullable=true)
     */
    private $data;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(
     *     name="claro_scheduled_task_users",
     *     joinColumns={@ORM\JoinColumn(name="scheduled_task_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection[]
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", nullable=true, onDelete="SET NULL")
     *
     * @var Group
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     *
     * @var Workspace
     */
    private $workspace;

    public function __construct()
    {
        $this->refreshUuid();

        $this->users = new ArrayCollection();
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getScheduledDate(): ?\DateTimeInterface
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(\DateTimeInterface $scheduledDate)
    {
        $this->scheduledDate = $scheduledDate;
    }

    public function isExecuted()
    {
        return !empty($this->executionDate);
    }

    public function getExecutionDate(): ?\DateTimeInterface
    {
        return $this->executionDate;
    }

    public function setExecutionDate(?\DateTimeInterface $executionDate = null)
    {
        $this->executionDate = $executionDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace = null)
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

    /**
     * Get group.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(Group $group = null)
    {
        $this->group = $group;
    }
}
