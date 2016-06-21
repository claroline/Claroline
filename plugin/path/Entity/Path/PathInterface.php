<?php

namespace Innova\PathBundle\Entity\Path;

/**
 * Path interface
 * All kind of paths should implement this interface or extends AbstractPath.
 */
interface PathInterface
{
    /**
     * Get unique identifier of the path.
     */
    public function getId();

    /**
     * Get name of the path.
     */
    public function getName();

    /**
     * Set name of the path.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get description of the path.
     */
    public function getDescription();

    /**
     * Set description of the path.
     *
     * @param string $description
     */
    public function setDescription($description);

    /**
     * Get structure.
     */
    public function getStructure();

    /**
     * Set structure of the path.
     *
     * @param string $structure
     */
    public function setStructure($structure);

    /**
     * Set breadcrumbs.
     *
     * @param bool $breadcrumbs
     *
     * @return \Innova\PathBundle\Entity\Path\AbstractPath
     */
    public function setBreadcrumbs($breadcrumbs);

    /**
     * Does Path have a breadcrumbs ?
     *
     * @return bool
     */
    public function hasBreadcrumbs();
}
