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
     * @var datetime $creationDate
     *
     * @ORM\Column(name="creation", type="datetime")
     */
    protected $creationDate;

    /**
     * @var datetime $modificationDate
     *
     * @ORM\Column(name="modification", type="datetime")
     */
    protected $modificationDate;

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

    /**
     * Pages associated with ExercisePlayer
     * @var pages
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Player\Page", cascade={"remove", "persist"}, mappedBy="exercisePlayer") 
     */
    protected $pages;

    public function __construct() {
        $this->published = false;
        $this->modified = false;
        $this->startDate = new \DateTime();
        $this->creationDate = new \DateTime();
        $this->modificationDate = new \DateTime();
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
     * Set player creation date
     *
     * @param datetime $date
     *  @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function setCreationDate($date) {
        $this->creationDate = $date;
        return $this;
    }

    /**
     * Get player creation date
     *
     * @return datetime
     */
    public function getCreationDate() {
        return $this->creationDate;
    }

    /**
     * Set player modification date
     *
     * @param datetime $date
     *  @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
     */
    public function setModificationDate($date) {
        $this->modificationDate = $date;
        return $this;
    }

    /**
     * Get player modification date
     *
     * @return datetime
     */
    public function getModificationDate() {
        return $this->modificationDate;
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

    /**
     * Set player published property
     * @param boolean $published
     * @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
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
     * @return \UJM\ExoBundle\Entity\Player\ExercisePlayer
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

    public function jsonSerialize() {
        // TODO serialize Pages arraycollection
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start' => $this->startDate,
            'end' => $this->endDate,
            'creation' => $this->creationDate,
            'modification' => $this->modificationDate,
            'published' => $this->published,
            'pages' => $this->pages
        );
    }

}
