<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
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
     *
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \UJM\ExoBundle\Entity\Misc\Label::class, mappedBy: 'interactionMatching', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private $labels;

    /**
     *
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \UJM\ExoBundle\Entity\Misc\Proposal::class, mappedBy: 'interactionMatching', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private $proposals;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \UJM\ExoBundle\Entity\Misc\Association::class, mappedBy: 'question', cascade: ['all'], orphanRemoval: true)]
    private $associations;

    /**
     * MatchQuestion constructor.
     */
    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->associations = new ArrayCollection();
    }

    /**
     * Gets associations.
     *
     * @return ArrayCollection
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Adds an association.
     */
    public function addAssociation(Association $association)
    {
        if (!$this->associations->contains($association)) {
            $this->associations->add($association);
            $association->setQuestion($this);
        }
    }

    /**
     * Removes an association.
     */
    public function removeAssociation(Association $association)
    {
        if ($this->associations->contains($association)) {
            $this->associations->removeElement($association);
        }
    }

    /**
     * Gets labels.
     *
     * @return ArrayCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Adds a label.
     */
    public function addLabel(Label $label)
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
            $label->setInteractionMatching($this);
        }
    }

    /**
     * Removes a label.
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }
    }

    /**
     * Gets proposals.
     *
     * @return ArrayCollection
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * Adds a proposal.
     */
    public function addProposal(Proposal $proposal)
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setInteractionMatching($this);
        }
    }

    /**
     * Removes a proposal.
     */
    public function removeProposal(Proposal $proposal)
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
        }
    }
}
