<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StepWho
 *
 * @ORM\Table("innova_stepWho")
 * @ORM\Entity
 */
class StepWho
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
    * @ORM\OneToMany(targetEntity="Step", mappedBy="stepWho")
    */
    protected $steps;

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
     * @param  string  $name
     * @return StepWho
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
     * Constructor
     */
    public function __construct()
    {
        $this->steps = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add steps
     *
     * @param  \Innova\PathBundle\Entity\Step $steps
     * @return StepWho
     */
    public function addStep(\Innova\PathBundle\Entity\Step $steps)
    {
        $this->steps[] = $steps;

        return $this;
    }

    /**
     * Remove steps
     *
     * @param \Innova\PathBundle\Entity\Step $steps
     */
    public function removeStep(\Innova\PathBundle\Entity\Step $steps)
    {
        $this->steps->removeElement($steps);
    }

    /**
     * Get steps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSteps()
    {
        return $this->steps;
    }
}
