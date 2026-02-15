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

use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\SchedulerBundle\Repository\ScheduledTaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_scheduled_task')]
#[ORM\Entity(repositoryClass: ScheduledTaskRepository::class)]
class ScheduledTask implements CrudEntityInterface
{
    use Id;
    use Name;
    use Uuid;

    /**
     * The task will be executed only once.
     */
    public const ONCE = 'once';

    /**
     * The task will be executed multiple times at the defined interval.
     */
    public const RECURRING = 'recurring';

    /**
     * The task is waiting for execution.
     */
    public const PENDING = 'pending';

    /**
     * The task is currently executed.
     */
    public const IN_PROGRESS = 'in_progress';

    /**
     * The task has been executed without errors.
     */
    public const SUCCESS = 'success';

    /**
     * The task has failed due to some errors.
     */
    public const ERROR = 'error';

    #[ORM\Column(name: 'task_action')]
    private ?string $action = null;

    #[ORM\Column(name: 'execution_type')]
    private string $executionType = self::ONCE;

    #[ORM\Column(name: 'scheduled_date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $scheduledDate = null;

    #[ORM\Column(name: 'execution_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $executionDate = null;

    /**
     * For recurring execution only, define an interval (in days) between each recurring execution.
     */
    #[ORM\Column(name: 'execution_interval', type: Types::INTEGER, nullable: true)]
    private ?int $executionInterval = null;

    /**
     * For recurring execution only, define when we will need to stop replaying the task.
     */
    #[ORM\Column(name: 'end_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(name: 'execution_status', nullable: true)]
    private ?string $status = self::PENDING;

    /**
     * The UUID of the object which have generated the task (e.g., an announcement id, an import/export id).
     */
    #[ORM\Column(name: 'parent_id', type: Types::STRING, nullable: true)]
    private ?string $parentId;

    #[ORM\Column(name: 'task_data', type: Types::JSON, nullable: true)]
    private ?array $data = [];

    /**
     * @var Collection<int, User>
     *
     * @deprecated
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'claro_scheduled_task_users')]
    #[ORM\JoinColumn(name: 'scheduled_task_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private Collection $users;

    /**
     * @deprecated
     */
    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'group_id', nullable: true, onDelete: 'SET NULL')]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    #[ORM\JoinColumn(name: 'workspace_id', nullable: true, onDelete: 'SET NULL')]
    private ?Workspace $workspace = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->users = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return [];
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getExecutionType(): string
    {
        return $this->executionType;
    }

    public function setExecutionType(string $type): void
    {
        $this->executionType = $type;
    }

    public function getScheduledDate(): ?\DateTimeInterface
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(\DateTimeInterface $scheduledDate): void
    {
        $this->scheduledDate = clone $scheduledDate;
    }

    public function getExecutionDate(): ?\DateTimeInterface
    {
        return $this->executionDate;
    }

    public function setExecutionDate(?\DateTimeInterface $executionDate = null): void
    {
        $this->executionDate = $executionDate ? clone $executionDate : null;
    }

    public function getExecutionInterval(): ?int
    {
        return $this->executionInterval;
    }

    public function setExecutionInterval(?int $executionInterval = null): void
    {
        $this->executionInterval = $executionInterval;
    }

    public function setEndDate(?\DateTimeInterface $endDate = null): void
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

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace = null): void
    {
        $this->workspace = $workspace;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId = null): void
    {
        $this->parentId = $parentId;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data = null): void
    {
        $this->data = $data;
    }

    public function getUsers(): array
    {
        return $this->users->toArray();
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(User $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }

    public function emptyUsers(): void
    {
        $this->users->clear();
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group = null): void
    {
        $this->group = $group;
    }
}
