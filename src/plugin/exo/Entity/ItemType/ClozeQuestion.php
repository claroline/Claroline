<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Hole;

/**
 * A Cloze question.
 */
#[ORM\Table(name: 'ujm_interaction_hole')]
#[ORM\Entity]
class ClozeQuestion extends AbstractItem
{
    /**
     * The HTML text with empty holes.
     */
    #[ORM\Column(name: 'htmlWithoutValue', type: Types::TEXT)]
    private ?string $text = null;

    /**
     * The list of holes present in the text.
     *
     * @var Collection<int, Hole>
     */
    #[ORM\OneToMany(targetEntity: Hole::class, mappedBy: 'interactionHole', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $holes;

    public function __construct()
    {
        $this->holes = new ArrayCollection();
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Gets holes.
     *
     * @return Hole[]|ArrayCollection
     */
    public function getHoles(): Collection
    {
        return $this->holes;
    }

    public function getHole(string $uuid): ?Hole
    {
        $found = null;
        foreach ($this->holes as $hole) {
            if ($hole->getUuid() === $uuid) {
                $found = $hole;
                break;
            }
        }

        return $found;
    }

    public function addHole(Hole $hole): void
    {
        if (!$this->holes->contains($hole)) {
            $this->holes->add($hole);
            $hole->setInteractionHole($this);
        }
    }

    public function removeHole(Hole $hole): void
    {
        if ($this->holes->contains($hole)) {
            $this->holes->removeElement($hole);
        }
    }
}
