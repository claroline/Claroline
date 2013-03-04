<?php

/**
 * ExoOnLine
 * Copyright or © or Copr. Université Jean Monnet (France), 2012
 * dsi.dev@univ-st-etienne.fr
 *
 * This software is a computer program whose purpose is to [describe
 * functionalities and technical features of your software].
 *
 * This software is governed by the CeCILL license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
*/

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Choice
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ChoiceRepository")
 * @ORM\Table(name="ujm_choice")
 */
class Choice
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
     * @var string $label
     *
     * @ORM\Column(name="label", type="text")
     */
    private $label;

    /**
     * @var integer $ordre
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * @var integer $weight
     *
     * @ORM\Column(name="weight", type="float", nullable=true)
     */
    private $weight;

    /**
     * @var text $feedback
     *
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    private $feedback;

     /**
     * @var boolean $rightResponse
     *
     * @ORM\Column(name="right_response", type="boolean", nullable=true)
     */
    private $rightResponse;

     /**
     * @var boolean $positionForce
     *
     * @ORM\Column(name="position_force", type="boolean", nullable=true)
     */
    private $positionForce;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\InteractionQCM", inversedBy="choices")
     * @ORM\JoinColumn(name="interaction_qcm_id", referencedColumnName="id")
     */
    private $interactionQCM;

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
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
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

    /**
     * Set weight
     *
     * @param integer $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set feedback
     *
     * @param text $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Get feedback
     *
     * @return text
     */
    public function getFeedback()
    {
        return $this->feedback;
    }


    public function getInteractionQCM()
    {
        return $this->interactionQCM;
    }

    public function setInteractionQCM(\UJM\ExoBundle\Entity\InteractionQCM $interactionQCM)
    {
        $this->interactionQCM = $interactionQCM;
    }

    /**
     * Set rightResponse
     *
     * @param boolean $rightResponse
     */
    public function setRightResponse($rightResponse)
    {
        $this->rightResponse = $rightResponse;
    }

    /**
     * Get rightResponse
     */
    public function getRightResponse()
    {
        return $this->rightResponse;
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
}