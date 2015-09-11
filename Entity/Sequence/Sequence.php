<?php

namespace UJM\ExoBundle\Entity\Sequence;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ExerciseBuilder Entity.
 *
 * @ORM\Table(name="ujm_sequence")
 * @ORM\Entity
 */
class Sequence extends AbstractResource implements \JsonSerializable
{
    /**
     * @var int
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
     * @var text
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var datetime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $startDate;

    /**
     * @var datetime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * Steps associated with the sequence.
     *
     * @var steps
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Sequence\Step", cascade={"remove", "persist"}, mappedBy="sequence") 
     */
    protected $steps;

    public function __construct()
    {
        $this->startDate = new \DateTime();
        $this->steps = new ArrayCollection();
    }

    /**
     * Get sequence Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Step $s
     *
     * @return \UJM\ExoBundle\Entity\Sequence\Sequence
     */
    public function addStep(Step $s)
    {
        $this->steps[] = $s;

        return $this;
    }

    /**
     * @param Step $s
     *
     * @return \UJM\ExoBundle\Entity\Sequence\Sequence
     */
    public function removePage(Step $s)
    {
        $this->steps->removeElement($s);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Set sequence name.
     *
     * @param string $name
     *
     * @return \UJM\ExoBundle\Entity\Sequence\Sequence
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get sequence name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set sequence startDate.
     *
     * @param datetime $startDate
     *
     * @return \UJM\ExoBundle\Entity\Sequence\Sequence
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get sequence startDate.
     *
     * @return datetime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set sequence endDate.
     *
     * @param datetime $endDate
     *
     *  @return \UJM\ExoBundle\Entity\Sequence\Sequence
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get sequence endDate.
     *
     * @return datetime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set sequence description.
     *
     * @param text $description
     *
     * @return \UJM\ExoBundle\Entity\Sequence\Sequence
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get sequence description.
     *
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'startDate' => !empty($this->startDate) ? $this->startDate->format('Y-m-d') : null,
            'endDate' => !empty($this->endDate) ? $this->endDate->format('Y-m-d') : null,
        );
    }
}
