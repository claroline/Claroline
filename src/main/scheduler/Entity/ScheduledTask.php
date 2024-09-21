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

#[ORM\Table(name: 'claro_scheduled_task')]
#[ORM\Entity(repositoryClass: \Claroline\SchedulerBundle\Repository\ScheduledTaskRepository::class)]
class ScheduledTask
{
    use Id;
    use Name;
    use Uuid;

    /**
     * The task will be executed only once.
     */
    const ONCE = 'once';

    /**
     * The task will be executed multiple times at the defined interval.
     */
    const RECURRING = 'recurring';

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

    #[ORM\Column(name: 'task_action')]
    private $action;

    #[ORM\Column(name: 'execution_type')]
    private $executionType = 'once';

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'scheduled_date', type: 'datetime')]
    private $scheduledDate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'execution_date', type: 'datetime', nullable: true)]
    private $executionDate = null;

    /**
     * For recurring execution only, define interval (in days) between each recurring execution.
     *
     *
     * @var int
     */
    #[ORM\Column(name: 'execution_interval', type: 'integer', nullable: true)]
    private $executionInterval = null;

    /**
     * For recurring execution only, define when we will need to stop replaying the task.
     *
     *
     * @var \DateTime
     */
    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    private $endDate = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'execution_status', nullable: true)]
    private $status = self::PENDING;

    /**
     * The UUID of the object which have generated the task (eg. an announcement id, an import/export id).
     *
     *
     * @var string
     */
    #[ORM\Column(name: 'parent_id', type: 'string', nullable: true)]
    private $parentId;

    #[ORM\Column(name: 'task_data', type: 'json', nullable: true)]
    private $data;

    /**
     *
     * @var ArrayCollection[]
     *
     * @deprecated
     */
    #[ORM\JoinTable(name: 'claro_scheduled_task_users')]
    #[ORM\JoinColumn(name: 'scheduled_task_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    private $users;

    /**
     *
     * @var Group
     *
     * @deprecated
     */
    #[ORM\JoinColumn(name: 'group_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Group::class)]
    private $group;

    /**
     *
     * @var Workspace
     */
    #[ORM\JoinColumn(name: 'workspace_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Workspace\Workspace::class)]
    private $workspace;

    public function __construct()
    {
        $this->refreshUuid();

        $this->users = new ArrayCollection();
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action)
    {
        $this->action = $action;
    }

    public function getExecutionType(): string
    {
        return $this->executionType;
    }

    public function setExecutionType(string $type)
    {
        $this->executionType = $type;
    }

    public function getScheduledDate(): ?\DateTimeInterface
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(\DateTimeInterface $scheduledDate)
    {
        $this->scheduledDate = clone $scheduledDate;
    }

    public function getExecutionDate(): ?\DateTimeInterface
    {
        return $this->executionDate;
    }

    public function setExecutionDate(?\DateTimeInterface $executionDate = null)
    {
        $this->executionDate = $executionDate ? clone $executionDate : null;
    }

    public function getExecutionInterval(): ?int
    {
        return $this->executionInterval;
    }

    public function setExecutionInterval(?int $executionInterval = null)
    {
        $this->executionInterval = $executionInterval;
    }

    public function setEndDate(?\DateTimeInterface $endDate = null)
    {
        $this->endDate = $endDate ? clone $endDate : null;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
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

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId = null)
    {
        $this->parentId = $parentId;
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
