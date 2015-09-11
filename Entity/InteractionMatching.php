<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * UJM\ExoBundle\Entity\InteractionMatching.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionMatchingRepository")
 * @ORM\Table(name="ujm_interaction_matching")
 */
class InteractionMatching
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Label", mappedBy="interactionMatching", cascade={"remove"})
     */
    private $labels;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Proposal", mappedBy="interactionMatching", cascade={"remove"})
     */
    private $proposals;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Interaction", cascade={"remove"})
     */
    private $interaction;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\TypeMatching")
     * @ORM\JoinColumn(name="type_matching_id", referencedColumnName="id")
     */
    private $typeMatching;

    /**
     * Constructs a new instance of label and proposal.
     */
    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->proposals = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set shuffle.
     *
     * @param bool $shuffle
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;
    }

    /**
     * Get shuffle.
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function getTypeMatching()
    {
        return $this->typeMatching;
    }

    public function setTypeMatching(\UJM\ExoBundle\Entity\TypeMatching $typeMatching)
    {
        $this->typeMatching = $typeMatching;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Add label.
     */
    public function addLabel(\UJM\ExoBundle\Entity\Label $label)
    {
        $this->labels[] = $label;
        //le label est bien lié à l'entité interactionmatching, mais dans l'entité label il faut
        //aussi lié l'interactionmatching double travail avec les relations bidirectionnelles avec
        //lesquelles il faut bien faire attention à garder les données cohérentes dans un autre
        //script il faudra exécuter $interactionmatching->addLabel() qui garde la cohérence entre les
        //deux entités, il ne faudra pas exécuter $label->setInteractionMatching(), car lui ne garde
        //pas la cohérence
        $label->setInteractionMatching($this);
    }

    /**
     * Get Proposals.
     *
     * @return Doctrine Collection of proposals
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * Add proposal.
     */
    public function addProposal(\UJM\ExoBundle\Entity\Proposal $proposal)
    {
        $this->proposals[] = $proposal;
        $proposal->setInteractionMatching($this);
    }

    /**
     * Clone this interactionMatching.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            $newLinkLabelProposal = array();

            $this->interaction = clone $this->interaction;

            $newLabels = new \Doctrine\Common\Collections\ArrayCollection();
            $newProposals = new \Doctrine\Common\Collections\ArrayCollection();

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

    public function shuffleProposals()
    {
        $this->sortProposals();
        $i = 0;
        $tabShuffle = array();
        $tabFixed = array();
        $proposals = new \Doctrine\Common\Collections\ArrayCollection();
        $proposalCount = count($this->proposals);

        while ($i < $proposalCount) {
            if ($this->proposals[$i]->getPositionForce() === false) {
                $tabShuffle[$i] = $i;
                $tabFixed[] = -1;
            } else {
                $tabFixed[] = $i;
            }
            ++$i;
        }

        shuffle($tabShuffle);

        $i = 0;
        $proposalCount = count($this->proposals);

        while ($i < $proposalCount) {
            if ($tabFixed[$i] != -1) {
                $proposals [] = $this->proposals[$i];
            } else {
                $index = $tabShuffle[0];
                $proposals[] = $this->proposals[$index];
                unset($tabShuffle[0]);
                $tabShuffle = array_merge($tabShuffle);
            }
            ++$i;
        }
        $this->proposals = $proposals;
    }

    public function sortProposals()
    {
        $tab = array();
        $proposals = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->proposals as $proposal) {
            $tab[] = $proposal->getOrdre();
        }

        asort($tab);

        foreach (array_keys($tab) as $indice) {
            $proposals[] = $this->proposals[$indice];
        }

        $this->proposals = $proposals;
    }

    public function shuffleLabels()
    {
        $this->sortLabels();

        $i = 0;
        $tabShuffle = array();
        $tabFixed = array();
        $labels = new \Doctrine\Common\Collections\ArrayCollection();
        $labelCount = count($this->labels);

        while ($i < $labelCount) {
            if ($this->labels[$i]->getPositionForce() === false) {
                $tabShuffle[$i] = $i;
                $tabFixed[] = -1;
            } else {
                $tabFixed[] = $i;
            }
            ++$i;
        }

        $i = 0;
        $labelCount = count($this->labels);

        shuffle($tabShuffle);

        while ($i < $labelCount) {
            if ($tabFixed[$i] != -1) {
                $labels [] = $this->labels[$i];
            } else {
                $index = $tabShuffle[0];
                $labels[] = $this->labels[$index];
                unset($tabShuffle[0]);
                $tabShuffle = array_merge($tabShuffle);
            }
            ++$i;
        }

        $this->labels = $labels;
    }

    public function sortLabels()
    {
        $tab = array();
        $labels = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->labels as $label) {
            $tab[] = $label->getOrdre();
        }

        asort($tab);

        foreach (array_keys($tab) as $indice) {
            $labels[] = $this->labels[$indice];
        }

        $this->labels = $labels;
    }
}
