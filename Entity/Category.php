<?php

namespace Innova\PathBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\PathBundle\Entity\Path\Path;

/**
 * Category
 *
 * @ORM\Table(name="innova_path_category")
 * @ORM\Entity()
 */
class Category implements \JsonSerializable
{
    /**
     * Unique identifier of the Category
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Name of the Category
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * Workspace
     * @var \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    protected $workspace;

    /**
     * List of Paths in this Category
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Innova\PathBundle\Entity\Path\Path", mappedBy="categories")
     */
    protected $paths;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->paths = new ArrayCollection();
    }

    /**
     * Get id.
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Workspace.
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set Workspace.
     * @param Workspace $workspace
     * @return $this
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get paths
     * @return ArrayCollection
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Add a new Path
     * @param Path $path
     * @return $this
     */
    public function addPath(Path $path)
    {
        if (!$this->paths->contains($path)) {
            $this->paths->add($path);

            // Update other side of the relation
            $path->addCategory($this);
        }

        return $this;
    }

    /**
     * Remove a Path
     * @param Path $path
     * @return $this
     */
    public function removePath(Path $path)
    {
        if ($this->paths->contains($path)) {
            $this->paths->removeElement($path);

            // Update other side of the relation
            $path->removeCategory($this);
        }

        return $this;
    }

    public function jsonSerialize()
    {
        return array (
            'id'   => $this->id,
            'name' => $this->name,
        );
    }
}