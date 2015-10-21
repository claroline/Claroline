<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * Category
 *
 *
 * @ORM\Table(name="innova_path_category")
 * @ORM\Entity()
 */
class Category
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
}