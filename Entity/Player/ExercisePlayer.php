<?php

namespace UJM\ExoBundle\Entity\Player;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use UJM\ExoBundle\Entity\Player\Page;

/**
 * ExercisePlayer Entity
 *
 * @ORM\Table(name="ujm_exercise_player")
 * @ORM\Entity
 */
class ExercisePlayer extends AbstractResource implements \JsonSerializable {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;   

    /**
     * @var datetime $startDate
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $startDate;

    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

   

    /**
     * Pages associated with ExercisePlayer
     * @var pages
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Player\Page", cascade={"remove", "persist"}, mappedBy="exercisePlayer") 
     */
    protected $pages;

    public function __construct() {       
        $this->startDate = new \DateTime();
        $this->pages = new ArrayCollection();
    }

    /**
     * Get player Id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * 
     * @param Page $p
     * @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function addPage(Page $p) {
        $this->pages[] = $p;
        return $this;
    }

    /**
     * 
     * @param Page $p
     * @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function removePage(Page $p) {
        $this->pages->removeElement($p);
        return $this;
    }

    /**
     * 
     * @return ArrayCollection
     */
    public function getPages() {
        return $this->pages;
    }

    /**
     * Set player name
     * @param string $name
     * @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Get player name
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set player startDate
     *
     * @param datetime $startDate
     * @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * Get player startDate
     *
     * @return datetime
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * Set player endDate
     *
     * @param datetime $endDate
     *  @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Get player endDate
     *
     * @return datetime
     */
    public function getEndDate() {
        return $this->endDate;
    }

    
    /**
     * Set player description
     *
     * @param text $description
     * @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Get player description
     *
     * @return text
     */
    public function getDescription() {
        return $this->description;
    }

    public function jsonSerialize() {
        
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'startDate' => !empty($this->startDate) ? $this->startDate->format('Y-m-d'): null,
            'endDate' => !empty($this->endDate) ? $this->endDate->format('Y-m-d'): null
        );
    }

}
