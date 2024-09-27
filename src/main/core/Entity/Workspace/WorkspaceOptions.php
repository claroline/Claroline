<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Workspace;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_workspace_options')]
#[ORM\Entity]
class WorkspaceOptions
{
    use Id;

    #[ORM\OneToOne(targetEntity: Workspace::class, inversedBy: 'options', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'workspace_id', nullable: true, onDelete: 'CASCADE')]
    private ?Workspace $workspace = null;

    /**
     * The options of the workspace.
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $details = [];

    /**
     * The list of items to display in the Workspace when shown.
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $breadcrumbItems = ['desktop', 'workspaces', 'current', 'tool'];

    /**
     * Get workspace.
     */
    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    /**
     * Set workspace.
     */
    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    /**
     * Get all Workspace options.
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * Set all workspace options.
     */
    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    public function getShowBreadcrumb(): bool
    {
        return !isset($this->details['hide_breadcrumb']) || !$this->details['hide_breadcrumb'];
    }

    public function setShowBreadcrumb(bool $show): void
    {
        $this->details['hide_breadcrumb'] = !$show;
    }

    public function getBreadcrumbItems(): ?array
    {
        return $this->breadcrumbItems;
    }

    public function setBreadcrumbItems(array $items): void
    {
        $this->breadcrumbItems = $items;
    }
}
