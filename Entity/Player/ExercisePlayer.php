<?php

namespace UJM\ExoBundle\Entity\Player;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * ExercisePlayer Entity
 *
 * @ORM\Table(name="ujm_exercise_player")
 * @ORM\Entity
 */
class ExercisePlayer extends AbstractResource {

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
    private $description;

   

    /**
     * @var datetime $startDate
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var datetime $endDate
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published", type="boolean")
     */
    protected $published;

    /**
     * @var boolean
     *
     * @ORM\Column(name="modified", type="boolean")
     */
    protected $modified;

    public function __construct() {
        $this->published = false;
        $this->modified = false;
        $this->startDate = new \DateTime();
    }

    /**
     * Get player Id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set player name
     * @param string $name
     * @return \UJM\ExoBundle\Entity\Player
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
     * @return \UJM\ExoBundle\Entity\Player
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
     *  @return \UJM\ExoBundle\Entity\Player
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
     * @return \UJM\ExoBundle\Entity\Player
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

    /**
     * Set player shuffle property
     *
     * @param boolean $shuffle
     * @return \UJM\ExoBundle\Entity\Player
     */
    public function setShuffle($shuffle) {
        $this->shuffle = $shuffle;
        return $this;
    }

    /**
     * Get player shuffle property
     * @return boolean
     */
    public function getShuffle() {
        return $this->shuffle;
    }

    /**
     * Set player published property
     * @param boolean $published
     * @return \UJM\ExoBundle\Entity\Player
     */
    public function setPublished($published) {
        $this->published = $published;
        return $this;
    }

    /**
     * Get player published property
     * @return boolean
     */
    public function getPublished() {
        return $this->published;
    }

    /**
     * Set player modified property
     * @param boolean $modified
     * @return \UJM\ExoBundle\Entity\Player
     */
    public function setModified($modified) {
        $this->modified = $modified;
        return $this;
    }

    /**
     * Get player modified property
     * @return boolean
     */
    public function getModified() {
        return $this->modified;
    }

}
