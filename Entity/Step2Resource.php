<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Step2Resource
 *
 * @ORM\Table("innova_step2resource")
 * @ORM\Entity
 */
class Step2Resource
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
    * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step")
    */
    private $step;


    /**
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Resource")
    */
    private $resourceNode;

    /**
     * @var integer
     *
     * @ORM\Column(name="resourceOrder", type="integer")
     */
    private $resourceOrder;
    
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
     * Set step
     *
     * @param \Innova\PathBundle\Entity\Step $step
     * @return Step2Resource
     */
    public function setStep(\Innova\PathBundle\Entity\Step $step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \Innova\PathBundle\Entity\Step 
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set resourceNode
     *
     * @param \Innova\PathBundle\Entity\Resource $resourceNode
     * @return Step2Resource
     */
    public function setResourceNode(\Innova\PathBundle\Entity\Resource $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    /**
     * Get resourceNode
     *
     * @return \Innova\PathBundle\Entity\Resource 
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * Set resourceOrder
     *
     * @param integer $resourceOrder
     * @return Step2Resource
     */
    public function setResourceOrder($resourceOrder)
    {
        $this->resourceOrder = $resourceOrder;

        return $this;
    }

    /**
     * Get resourceOrder
     *
     * @return integer 
     */
    public function getResourceOrder()
    {
        return $this->resourceOrder;
    }
}
