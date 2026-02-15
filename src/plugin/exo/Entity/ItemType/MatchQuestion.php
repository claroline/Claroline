<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Association;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
use UJM\ExoBundle\Library\Model\PenaltyTrait;
use UJM\ExoBundle\Library\Model\ShuffleTrait;

/**
 * A Match question.
 */
#[ORM\Table(name: 'ujm_interaction_matching')]
#[ORM\Entity]
class MatchQuestion extends AbstractItem
{
    use ShuffleTrait;
    /*
     * The penalty to apply to each wrong association
     */
    use PenaltyTrait;

    /**
     * @var Collection<int, Label>
     */
    #[ORM\OneToMany(targetEntity: Label::class, mappedBy: 'interactionMatching', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $labels;

    /**
     * @var Collection<int, Proposal>
     */
    #[ORM\OneToMany(targetEntity: Proposal::class, mappedBy: 'interactionMatching', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $proposals;

    /**
     * @var Collection<int, Association>
     */
    #[ORM\OneToMany(targetEntity: Association::class, mappedBy: 'question', cascade: ['all'], orphanRemoval: true)]
    private Collection $associations;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->associations = new ArrayCollection();
    }

    public function getAssociations(): Collection
    {
        return $this->associations;
    }

    public function addAssociation(Association $association): void
    {
        if (!$this->associations->contains($association)) {
            $this->associations->add($association);
            $association->setQuestion($this);
        }
    }

    public function removeAssociation(Association $association): void
    {
        if ($this->associations->contains($association)) {
            $this->associations->removeElement($association);
        }
    }

    public function getLabels(): Collection
    {
        return $this->labels;
    }

    public function addLabel(Label $label): void
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
            $label->setInteractionMatching($this);
        }
    }

    public function removeLabel(Label $label): void
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }
    }

    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): void
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setInteractionMatching($this);
        }
    }

    public function removeProposal(Proposal $proposal): void
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
        }
    }
}
