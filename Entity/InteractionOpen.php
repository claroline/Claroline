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
 * UJM\ExoBundle\Entity\InteractionOpen
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionOpenRepository")
 * @ORM\Table(name="ujm_interaction_open")
 */
class InteractionOpen
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
     * @var boolean $orthographyCorrect
     *
     * @ORM\Column(name="orthography_correct", type="boolean")
     */
    private $orthographyCorrect;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Interaction", cascade={"remove"})
     */
    private $interaction;

    
    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\TypeOpenQuestion")
     */
    private $typeopenquestion;
    
    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\WordResponse", mappedBy="interactionopen", cascade={"remove"})
     */
    private $wordResponses;
    
    /**
     * @var float $scoreMaxLongResp
     *
     * @ORM\Column(name="scoreMaxLongResp", type="float", nullable=true)
     */
    private $scoreMaxLongResp;

    public function __construct()
    {
        $this->wordResponses = new \Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * Set orthographyCorrect
     *
     * @param boolean $orthographyCorrect
     */
    public function setOrthographyCorrect($orthographyCorrect)
    {
        $this->orthographyCorrect = $orthographyCorrect;
    }

    /**
     * Get orthographyCorrect
     */
    public function getOrthographyCorrect()
    {
        return $this->orthographyCorrect;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function getTypeOpenQuestion()
    {
        return $this->typeopenquestion;
    }

    public function setTypeOpenQuestion(\UJM\ExoBundle\Entity\TypeOpenQuestion $typeOpenQuestion)
    {
        $this->typeopenquestion = $typeOpenQuestion;
    }
    
    public function getWordResponses()
    {
        
        return $this->wordResponses;
    }

    public function addWordResponse(\UJM\ExoBundle\Entity\WordResponse $wordResponse)
    {
        $this->wordResponses[] = $wordResponse;

        $wordResponse->setInteractionOpen($this);
    }

    public function removeWordResponse(\UJM\ExoBundle\Entity\WordResponse $wordResponse)
    {
        
    }
    
    /**
     * Set scoreMaxLongResp
     *
     * @param float $scoreMaxLongResp
     */
    public function setScoreMaxLongResp($scoreMaxLongResp)
    {
        $this->scoreMaxLongResp = $scoreMaxLongResp;
    }

    /**
     * Get scoreMaxLongResp
     */
    public function getScoreMaxLongResp()
    {
        return $this->scoreMaxLongResp;
    }
}