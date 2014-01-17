<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Path
 *
 * @ORM\Table(name="innova_path")
 * @ORM\Entity(repositoryClass="Innova\PathBundle\Repository\PathRepository")
 */
class Path extends AbstractResource
{
    const DEFAULT_NAME = 'My path';
    
    /**
     * JSON structure of the path
     * @var string
     *
     * @ORM\Column(name="structure", type="text")
     */
    private $structure;

    /**
     * Steps linked to the path
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Step", mappedBy="path")
     */
    protected $steps;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deployed", type="boolean")
     */
    private $deployed;

    /**
     * @var boolean
     *
     * @ORM\Column(name="modified", type="boolean")
     */
    private $modified;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->deployed = false;
        $this->modified = false;
    }
    
    /**
     * Set json structure
     * @param  string $path
     * @return \Innova\PathBundle\Entity\Path
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get JSON structure
     * @return string
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set deployed
     * @param  boolean $deployed
     * @return \Innova\PathBundle\Entity\Path
     */
    public function setDeployed($deployed)
    {
        $this->deployed = $deployed;

        return $this;
    }

    /**
     * Is path already deployed
     * @return boolean
     */
    public function isDeployed()
    {
        return $this->deployed;
    }

    /**
     * Set modified
     * @param  boolean $modified
     * @return \Innova\PathBundle\Entity\Path
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Is path modified since the last deployement
     * @return boolean
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     * Add step
     * @param  \Innova\PathBundle\Entity\Step $step
     * @return \Innova\PathBundle\Entity\Path
     */
    public function addStep(\Innova\PathBundle\Entity\Step $step)
    {
        $this->steps[] = $step;
        $step->setPath($this);
        
        return $this;
    }

    /**
     * Remove step
     * @param \Innova\PathBundle\Entity\Step $step
     */
    public function removeStep(\Innova\PathBundle\Entity\Step $step)
    {
        $this->steps->removeElement($step);
        
        return $this;
    }

    /**
     * Get steps
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set description
     * @param string $description
     * @return \Innova\PathBundle\Entity\Path
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * Get root step of the path
     * @throws \Exception
     * @return \Innova\PathBundle\Entity\Step
     */
    public function getRootStep()
    {
        if (empty($this->steps)) {
            // Current path has no step
            throw new \Exception('Unable to find root Step for ' . get_class($this) . ' (ID = ' . $this->id . '). Path has no step.');
        }
        
        $root = null;
        foreach ($this->steps as $step)
        {
            if ($step->getParent() === null) {
                // Root step found
                $root = $step;
                break;
            }
        }
        
        if (empty($root)) {
            // Unable to find root step in steps list
            throw new \Exception('Unable to find root Step for ' . get_class($this) . ' (ID = ' . $this->id . ').');
        }
        
        return $root;
    }
    
    /**
     * Initialize a new path entity with required info and structure
     * @param string $name
     * @return \Innova\PathBundle\Entity\Path
     */
    public static function initialize($name = null)
    {
        $path = new Path();
        if (empty($name)) {
            $name = static::DEFAULT_NAME;
        }
    
        $path->setName($name);
    
        // Init path structure
        $structure = $path->initializeStructure();
        $path->setStructure($structure);
    
        return $path;
    }
    
    public function initializeStructure()
    {
        $structure = array (
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'steps' =>
            array (
                array (
                    'id' => 1,
                    'name' => $this->getName(),
                ),
            ),
        );
    
        return json_encode($structure);
    }
}
