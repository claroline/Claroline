<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Entity\Rules;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro__open_badge_rule')]
#[ORM\Entity]
class Rule
{
    use Id;
    use Uuid;

    public const RESOURCE_SCORE_ABOVE = 'resource_score_above';
    public const RESOURCE_COMPLETED_ABOVE = 'resource_completed_above';
    public const RESOURCE_STATUS = 'resource_status';

    public const WORKSPACE_SCORE_ABOVE = 'workspace_score_above';
    public const WORKSPACE_COMPLETED_ABOVE = 'workspace_completed_above';
    public const WORKSPACE_STATUS = 'workspace_status';

    public const IN_GROUP = 'in_group';
    public const IN_ROLE = 'in_role';

    #[ORM\Column(type: Types::STRING, nullable: false)]
    protected ?string $action = null;

    
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BadgeClass::class, inversedBy: 'rules')]
    private ?BadgeClass $badge = null;

    #[ORM\Column(type: Types::JSON)]
    private array $data = [];

    
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $node = null;

    
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Role::class)]
    private ?Role $role = null;

    
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Group::class)]
    private ?Group $group = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setData(array $data = []): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setBadge(BadgeClass $badge): void
    {
        $this->badge = $badge;
    }

    public function getBadge(): ?BadgeClass
    {
        return $this->badge;
    }

    public function setResourceNode(?ResourceNode $node = null): void
    {
        $this->node = $node;
    }

    public function getResourceNode(): ?ResourceNode
    {
        return $this->node;
    }

    public function setWorkspace(?Workspace $workspace = null): void
    {
        $this->workspace = $workspace;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setGroup(?Group $group = null): void
    {
        $this->group = $group;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }
}
