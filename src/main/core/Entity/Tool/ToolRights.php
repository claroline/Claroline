<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Repository\Tool\ToolRightsRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_tool_rights')]
#[ORM\UniqueConstraint(name: 'tool_rights_unique_ordered_tool_role', columns: ['ordered_tool_id', 'role_id'])]
#[ORM\Entity(repositoryClass: ToolRightsRepository::class)]
class ToolRights
{
    use Id;

    #[ORM\Column(type: Types::INTEGER)]
    private int $mask = 0;

    
    #[ORM\JoinColumn(name: 'role_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Role::class)]
    private Role $role;

    /**
     * should be unidirectional imo.
     *
     *
     */
    #[ORM\JoinColumn(name: 'ordered_tool_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: OrderedTool::class, inversedBy: 'rights')]
    private ?OrderedTool $orderedTool = null;

    public function getMask(): int
    {
        return $this->mask;
    }

    public function setMask(int $mask): void
    {
        $this->mask = $mask;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function getOrderedTool(): ?OrderedTool
    {
        return $this->orderedTool;
    }

    /**
     * @internal
     */
    public function setOrderedTool(OrderedTool $orderedTool): void
    {
        $this->orderedTool = $orderedTool;
    }
}
