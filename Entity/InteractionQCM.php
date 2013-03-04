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
 * UJM\ExoBundle\Entity\InteractionQCM
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionQCMRepository")
 * @ORM\Table(name="ujm_interaction_qcm")
 */
class InteractionQCM
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
     * @var boolean $shuffle
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle;

    /**
     * @var float $scoreRightResponse
     *
     * @ORM\Column(name="score_right_response", type="float", nullable=true)
     */
    private $scoreRightResponse;

    /**
     * @var float $scoreFalseResponse
     *
     * @ORM\Column(name="score_false_response", type="float", nullable=true)
     */
    private $scoreFalseResponse;

     /**
     * @var boolean $weightResponse
     *
     * @ORM\Column(name="weight_response", type="boolean", nullable=true)
     */
    private $weightResponse;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Choice", mappedBy="interactionQCM", cascade={"remove"})
     */
    private $choices;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Interaction", cascade={"remove"})
     */
    private $interaction;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\TypeQCM")
     * @ORM\JoinColumn(name="type_qcm_id", referencedColumnName="id")
     */
    private $typeQCM;

    /**
     * Constructs a new instance of choices
     */
    public function __construct()
    {
        $this->choices = new \Doctrine\Common\Collections\ArrayCollection;
        $this->setShuffle(false);
        $this->setWeightResponse(false);
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

    public function getTypeQCM()
    {
        return $this->typeQCM;
    }

    public function setTypeQCM(\UJM\ExoBundle\Entity\TypeQCM $typeQCM)
    {
        $this->typeQCM = $typeQCM;
    }

    /**
     * Set shuffle
     *
     * @param boolean $shuffle
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;
    }

    /**
     * Get shuffle
     *
     * @return boolean
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set scoreRightResponse
     *
     * @param float $scoreRightResponse
     */
    public function setScoreRightResponse($scoreRightResponse)
    {
        $this->scoreRightResponse = $scoreRightResponse;
    }

    /**
     * Get scoreRightResponse
     *
     * @return float
     */
    public function getScoreRightResponse()
    {
        return $this->scoreRightResponse;
    }

    /**
     * Set scoreFalseResponse
     *
     * @param float $scoreFalseResponse
     */
    public function setScoreFalseResponse($scoreFalseResponse)
    {
        $this->scoreFalseResponse = $scoreFalseResponse;
    }

    /**
     * Get scoreFalseResponse
     *
     * @return float
     */
    public function getScoreFalseResponse()
    {
        return $this->scoreFalseResponse;
    }

    /**
     * Set weightResponse
     *
     * @param boolean $weightResponse
     */
    public function setWeightResponse($weightResponse)
    {
        $this->weightResponse = $weightResponse;
    }

    /**
     * Get weightResponse
     *
     * @return boolean
     */
    public function getWeightResponse()
    {
        return $this->weightResponse;
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function addChoice(\UJM\ExoBundle\Entity\Choice $choice)
    {
        $this->choices[] = $choice;
        //le choix est bien lié à l'entité interactionqcm, mais dans l'entité choice il faut
        //aussi lié l'interactionqcm double travail avec les relations bidirectionnelles avec
        //lesquelles il faut bien faire attention à garder les données cohérentes dans un autre
        //script il faudra exécuter $interactionqcm->addChoice() qui garde la cohérence entre les
        //deux entités, il ne faudra pas exécuter $choice->setInteractionQCM(), car lui ne garde
        //pas la cohérence
        $choice->setInteractionQCM($this);
    }

    public function shuffleChoices()
    {
        $this->sortChoices();
        $i = 0;
        $tabShuffle = array();
        $tabFixed   = array();
        $tab        = array();
        $choices = new \Doctrine\Common\Collections\ArrayCollection;
        $choiceCount = count($this->choices);

        while ($i < $choiceCount) {
            if ($this->choices[$i]->getPositionForce() === false) {
                $tabShuffle[$i] = $i;
                $tabFixed[] = -1;
            } else {
                $tabFixed[] = $i;
            }

            $i++;
        }
        shuffle($tabShuffle);

        $i = 0;
        $choiceCount = count($this->choices);

        while ($i < $choiceCount) {
            if ($tabFixed[$i] != -1) {
                $choices[] = $this->choices[$i];
            } else {
                $index = $tabShuffle[0];
                $choices[] = $this->choices[$index];
                unset($tabShuffle[0]);
                $tabShuffle = array_merge($tabShuffle);
            }

            $i++;
        }

        $this->choices = $choices;
    }

    public function sortChoices()
    {
        $tab = array();
        $choices = new \Doctrine\Common\Collections\ArrayCollection;

        foreach ($this->choices as $choice) {
            $tab[] = $choice->getOrdre();
        }

        asort($tab);

        foreach ($tab as $indice => $valeur) {
            $choices[] = $this->choices[$indice];
        }

        $this->choices = $choices;
    }
}