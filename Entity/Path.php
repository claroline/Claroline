<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
/**
 * Path
 *
 * @ORM\Table(name="innova_path")
 * @ORM\Entity(repositoryClass="Innova\PathBundle\Repository\PathRepository")
 */
class Path extends AbstractResource
{

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text")
     */
    private $path;

    /**
    * @ORM\OneToMany(targetEntity="Step", mappedBy="path")
    */
    protected $steps;

    /**
     * Set path
     *
     * @param  string $path
     * @return Path
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set uuid
     *
     * @param  string $uuid
     * @return Path
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->steps = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add steps
     *
     * @param  \Innova\PathBundle\Entity\Step $steps
     * @return Path
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

    /**
     * Set resourceNode
     *
     * @param  \Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode
     * @return Path
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
}
