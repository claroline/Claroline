<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * UJM\ExoBundle\Entity\InteractionMatching
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionMatchingRepository")
 * @ORM\Table(name="ujm_interaction_matching")
 */
class InteractionMatching
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * Constructs a new instance of label and proposal
     */
    public function __construct()
    {
        $this->labels   = new ArrayCollection;
        $this->proposals = new ArrayCollection;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * Add label
     *
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
     * Get Proposals
     *
     * @return Doctrine Collection of proposals
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * Add proposal
     *
     */
    public function addProposal(\UJM\ExoBundle\Entity\Proposal $proposal)
    {
        $this->proposals[] = $proposal;
        $proposal->setInteractionMatching($this);
    }

    /**
     * Clone this interactionMatching
     *
     */
    public function __clone() {
    }
}
