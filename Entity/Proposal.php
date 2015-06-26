<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Proposal
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ProposalRepository")
 * @ORM\Table(name="ujm_proposal")
 */
class Proposal
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
     * @var string $value
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @var boolean $positionForce
     *
     * @ORM\Column(name="position_force", type="boolean", nullable=true)
     */
    private $positionForce;

     /**
     * @var integer $ordre
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\InteractionMatching", inversedBy="proposals")
     * @ORM\JoinColumn(name="interaction_matching_id", referencedColumnName="id")
     */
    private $interactionMatching;

    /**
     * @ORM\ManyToMany(targetEntity="UJM\ExoBundle\Entity\Label")
     * @ORM\JoinColumn(name="label_id", referencedColumnName="id")
     * @ORM\JoinTable(name="ujm_proposal_label")
     */
    private $associatedLabel;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get InteractionMatching
     *
     * @return InteractionMatching
     */
    public function getInteractionMatching()
    {
        return $this->interactionMatching;
    }

    /**
     * Set InteractionMatching
     *
     */
    public function setInteractionMatching(\UJM\ExoBundle\Entity\InteractionMatching $interactionMatching)
    {
        $this->interactionMatching = $interactionMatching;
    }

    /**
     * Get InteractionMatching
     *
     * @return Label
     */
    public function getAssociatedLabel()
    {
        return $this->associatedLabel;
    }

    /**
     * Set Label
     *
     */
    public function addAssociatedLabel(\UJM\ExoBundle\Entity\Label $label)
    {
        $this->associatedLabel[] = $label;
    }

    /**
     * Remove Label
     *
     */
    public function removeAssociatedLabel()
    {
        $this->associatedLabel = NULL;
    }

    /**
     * Set positionForce
     *
     * @param boolean $positionForce
     */
    public function setPositionForce($positionForce)
    {
        $this->positionForce = $positionForce;
    }

    /**
     * Get positionForce
     */
    public function getPositionForce()
    {
        return $this->positionForce;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

}
