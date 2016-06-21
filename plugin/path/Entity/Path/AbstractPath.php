<?php

namespace Innova\PathBundle\Entity\Path;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** 
 * Abstract path.
 *  
 * @ORM\MappedSuperclass 
 */
class AbstractPath
{
    /**
     * Name of the path.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * Display a breadcrumbs for navigation into the Path.
     *
     * @var bool
     *
     * @ORM\Column(name="breadcrumbs", type="boolean")
     */
    protected $breadcrumbs = false;

    /**
     * JSON structure of the path.
     *
     * @var string
     *
     * @ORM\Column(name="structure", type="text")
     */
    protected $structure;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return \Innova\PathBundle\Entity\Path\AbstractPath
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set breadcrumbs.
     *
     * @param bool $breadcrumbs
     *
     * @return \Innova\PathBundle\Entity\Path\AbstractPath
     */
    public function setBreadcrumbs($breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    /**
     * Does Path have a breadcrumbs ?
     *
     * @return bool
     */
    public function hasBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * Set structure.
     *
     * @param string $structure
     *
     * @return \Innova\PathBundle\Entity\Path\AbstractPath
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure.
     *
     * @return string
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
