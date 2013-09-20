<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Step2ResourceNode
 *
 * @ORM\Table("innova_step2resourceNode")
 * @ORM\Entity
 */
class Step2ResourceNode
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
    */
    private $resourceNode;



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
     * @var integer
     *
     * @ORM\Column(name="resourceOrder", type="integer")
     */
    private $resourceOrder;


    /**
     * Set step
     *
     * @param \Innova\PathBundle\Entity\Step $step
     * @return Step2ResourceNode
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
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode
     * @return Step2ResourceNode
     */
    public function setResourceNode(\Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    /**
     * Get resourceNode
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode 
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * Set resourceOrder
     *
     * @param integer $resourceOrder
     * @return Step2ResourceNode
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
