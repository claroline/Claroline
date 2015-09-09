<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionMatchingRepository")
 * @ORM\Table(name="ujm_interaction_matching")
 */
class InteractionMatching extends AbstractInteraction
{
    /**
     * @ORM\Column(type="boolean")
     */
    private $shuffle = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Label",
     *     mappedBy="interactionMatching",
     *     cascade={"remove"}
     * )
     */
    private $labels;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Proposal",
     *     mappedBy="interactionMatching",
     *     cascade={"remove"}
     * )
     */
    private $proposals;

    /**
     * @ORM\ManyToOne(targetEntity="TypeMatching")
     * @ORM\JoinColumn(name="type_matching_id", referencedColumnName="id")
     */
    private $typeMatching;

    /**
     * Constructs a new instance of label and proposal
     */
    public function __construct()
    {
        $this->labels   = new ArrayCollection;
        $this->proposals = new ArrayCollection;
    }

    /**
     * @param boolean $shuffle
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;
    }

    /**
     * @return boolean
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * @return TypeMatching
     */
    public function getTypeMatching()
    {
        return $this->typeMatching;
    }

    /**
     * @param TypeMatching $typeMatching
     */
    public function setTypeMatching(TypeMatching $typeMatching)
    {
        $this->typeMatching = $typeMatching;
    }

    /**
     * @return ArrayCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param Label $label
     */
    public function addLabel(Label $label)
    {
        $this->labels->add($label);
        $label->setInteractionMatching($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * @param Proposal $proposal
     */
    public function addProposal(Proposal $proposal)
    {
        $this->proposals->add($proposal);
        $proposal->setInteractionMatching($this);
    }

    public function shuffleProposals ()
    {
        $this->sortProposals();
        $i = 0;
        $tabShuffle = array();
        $tabFixed   = array();
        $proposals = new \Doctrine\Common\Collections\ArrayCollection;
        $proposalCount = count($this->proposals);

        while ( $i < $proposalCount ) {
            if ( $this->proposals[$i]->getPositionForce() === false ) {
                $tabShuffle[$i] = $i;
                $tabFixed[] = -1;
            } else {
                $tabFixed[] = $i;
            }
            $i++;
        }

        shuffle($tabShuffle);

        $i = 0;
        $proposalCount = count($this->proposals);

        while ( $i < $proposalCount ) {
            if ( $tabFixed[$i] != -1 ) {
                $proposals [] = $this->proposals[$i];
            } else {
                $index = $tabShuffle[0];
                $proposals[] = $this->proposals[$index];
                unset($tabShuffle[0]);
                $tabShuffle = array_merge($tabShuffle);
            }
            $i++;
        }
        $this->proposals = $proposals;
    }

    public function sortProposals()
    {
        $tab = [];
        $proposals = new ArrayCollection;

        foreach ($this->proposals as $proposal) {
            $tab[] = $proposal->getOrdre();
        }

        asort($tab);

        foreach (array_keys($tab) as $indice) {
            $proposals[] = $this->proposals[$indice];
        }

        $this->proposals = $proposals;
    }

    public function shuffleLabels ()
    {
        $this->sortLabels();

        $i = 0;
        $tabShuffle = [];
        $tabFixed = [];
        $labels = new ArrayCollection;
        $labelCount = count($this->labels);

        while ( $i < $labelCount ) {
            if ( $this->labels[$i]->getPositionForce() === false ) {
                $tabShuffle[$i] = $i;
                $tabFixed[] = -1;
            } else {
                $tabFixed[] = $i;
            }
            $i++;
        }

        $i = 0;
        $labelCount = count($this->labels);

        shuffle($tabShuffle);

        while ( $i < $labelCount ) {
          if ($tabFixed[$i] != -1) {
                $labels [] = $this->labels[$i];
            } else {
                $index = $tabShuffle[0];
                $labels[] = $this->labels[$index];
                unset($tabShuffle[0]);
                $tabShuffle = array_merge($tabShuffle);
            }
            $i++;
        }

        $this->labels = $labels;
    }

    public function sortLabels ()
    {
        $tab = [];
        $labels = new ArrayCollection;

        foreach ($this->labels as $label ) {
            $tab[] = $label->getOrdre();
        }

        asort($tab);

        foreach (array_keys($tab) as $indice) {
            $labels[] = $this->labels[$indice];
        }

        $this->labels = $labels;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $newLinkLabelProposal = [];
            $this->question = clone $this->question;
            $newLabels = new ArrayCollection;
            $newProposals = new ArrayCollection;

            foreach ($this->labels as $label) {
                $newLabel = clone $label;
                $newLabel->setInteractionMatching($this);
                $newLabels->add($newLabel);
                $newLinkLabelProposal[$label->getId()] = $newLabel;
            }

            $this->labels = $newLabels;

            foreach ($this->proposals as $proposal) {
                $newProposal = clone $proposal;
                $newProposal->removeAssociatedLabel();
                $newProposal->setInteractionMatching($this);
                $newProposals->add($newProposal);

                if ($proposal->getAssociatedLabel() != null) {
                    $assocedLabel = $proposal->getAssociatedLabel();
                    foreach ($assocedLabel as $assocLabel) {
                        $newProposal->addAssociatedLabel(
                            $newLinkLabelProposal[$assocLabel->getId()]
                        );
                    }
                }
            }

            $this->proposals = $newProposals;
        }
    }
}
