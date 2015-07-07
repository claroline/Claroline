<?php

namespace UJM\ExoBundle\Entity\Player;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

use UJM\ExoBundle\Entity\Player\ExercisePlayer;

/**
 * Page Entity
 *
 * @ORM\Table(name="ujm_exercise_page")
 * @ORM\Entity
 */
class Page implements \JsonSerializable{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * Page position / order in the ExercisePlayer sequence 
     * first and last page are note ordered
     * 
     * @var Number $position
     *
     * @ORM\Column(name="position", type="smallint", nullable=true)
     * @Assert\NotBlank
     */
    protected $position;
    
    /**
     * 
     * @var boolean
     * @ORM\Column(name="shuffle", type="boolean")
     */
    protected $shuffle;
    
    /**
     *
     * @var boolean
     * @ORM\Column(name="is_first_page", type="boolean") 
     */
    protected $isFirstPage;
    
     /**
     *
     * @var boolean
     * @ORM\Column(name="is_last_page", type="boolean") 
     */
    protected $isLastPage;


    /**
     * @var ExercisePlayer 
     * 
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Player\ExercisePlayer", inversedBy="pages")
     * @ORM\JoinColumn(name="exercise_player_id", nullable=false)
     */
    protected $exercisePlayer;
    

    public function __construct() {
        $this->shuffle = false;
        $this->isFirstPage = false;
        $this->isLastPage = false;
    }

    /**
     * Get page Id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    public function getDescription(){
        return $this->description;
    }
    
    /**
     * 
     * @param string $description
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function setDescription($description){
        $this->description = $description;
        return $this;
    }
    
    /**
     * 
     * @param ExercisePlayer $exoplayer
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function setExercisePlayer(ExercisePlayer $exoplayer){        
        $this->exercisePlayer = $exoplayer;
        return $this;
    }
    
    /**
     * 
     * @return ExercisePlayer
     */
    public function getExercisePlayer(){
        return $this->exercisePlayer;
    }

    /**
     * 
     * @param integer $position
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }
    
    /**
     * 
     * @return integer
     */
    public function getPosition(){
        return $this->position;
    }
    
    /**
     * 
     * @param boolean $shuffle
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function setShuffle($shuffle){
        $this->shuffle = $shuffle;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getShuffle(){
        return $this->shuffle;
    }
    
    /**
     * 
     * @param boolean $isLast
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function setIsLastPage($isLast){
        $this->isLastPage = $isLast;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getIsLastPage(){
        return $this->isLastPage;
    }
    
    /**
     * 
     * @param boolean $isFirst
     * @return \UJM\ExoBundle\Entity\Player\Page
     */
    public function setIsFirstPage($isFirst){
        $this->isFirstPage = $isFirst;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getIsFirstPage(){
        return $this->isFirstPage;
    }

    public function jsonSerialize()
    {
        // TODO serialize questions arraycollection
        return array (
            'id'            => $this->id,
            'position'      => $this->position,
            'shuffle'       => $this->shuffle,
            'isFirst'       => $this->isFirstPage,
            'isLast'        => $this->isLastPage,
            'description'   => $this->description
        );
    }

}
