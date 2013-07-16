<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OperatorType
 *
 * @ORM\Table(name="innova_operatortype")
 * @ORM\Entity
 */
class OperatorType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Acivity")
     * @ORM\JoinTable(name="innova_operatortype_activitiy",
     *      joinColumns={@ORM\JoinColumn(name="operatortype_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="activity_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $activities;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activities = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return StepType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return StepType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add activities
     *
     * @param \Innova\PathBundle\Entity\AbstractWorkspace $activities
     * @return AbstractRessource
     */
    public function addActivity(\Innova\PathBundle\Entity\Activity $activities)
    {
        $this->activities[] = $activities;

        return $this;
    }

    /**
     * Remove activities
     *
     * @param \Innova\PathBundle\Entity\Activity $activities
     */
    public function removeActivity(\Innova\PathBundle\Entity\Activity $activities)
    {
        $this->activities->removeElement($activities);
    }

    /**
     * Get activities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActivities()
    {
        return $this->activities;
    }
}
