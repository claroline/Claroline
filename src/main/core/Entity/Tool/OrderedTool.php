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

use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Hidden;
use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\HasContext;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_ordered_tool')]
#[ORM\Entity(repositoryClass: OrderedToolRepository::class)]
#[ORM\UniqueConstraint(name: 'context_unique_tool', columns: ['tool_name', 'context_name', 'context_id'])]
class OrderedTool implements CrudEntityInterface
{
    use Id;
    use Uuid;
    use HasContext;
    // meta
    use Thumbnail;
    use Poster;
    use Order;
    use Hidden;

    #[ORM\Column(name: 'tool_name', type: Types::STRING, nullable: false)]
    private ?string $name;

    /**
     * Display tool icon when the tool is rendered.
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $showIcon = false;

    /**
     * Display in fullscreen when the tool is opened.
     */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $fullscreen = false;

    /**
     * @var Collection<int, ToolRights>
     */
    #[ORM\OneToMany(targetEntity: ToolRights::class, mappedBy: 'orderedTool')]
    private Collection $rights;

    public function __construct()
    {
        $this->refreshUuid();

        $this->rights = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return [];
    }

    public function getMimeType(): string
    {
        if ($this->name) {
            return 'tool.'.$this->name;
        }

        return 'tool';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getShowIcon(): bool
    {
        return $this->showIcon;
    }

    public function setShowIcon(bool $showIcon): void
    {
        $this->showIcon = $showIcon;
    }

    public function getFullscreen(): bool
    {
        return $this->fullscreen;
    }

    public function setFullscreen(bool $fullscreen): void
    {
        $this->fullscreen = $fullscreen;
    }

    public function getRights(): Collection
    {
        return $this->rights;
    }

    public function addRight(ToolRights $right): void
    {
        if (!$this->rights->contains($right)) {
            $this->rights->add($right);
            $right->setOrderedTool($this);
        }
    }

    public function removeRight(ToolRights $right): void
    {
        if ($this->rights->contains($right)) {
            $this->rights->removeElement($right);
        }
    }
}
