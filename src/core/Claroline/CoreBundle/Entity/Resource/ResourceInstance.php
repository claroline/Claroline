<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceInstanceRepository")
 * @ORM\Table(name="claro_resource_instance")
 * @Gedmo\Tree(type="materializedPath")
 */
class ResourceInstance
{
    const PATH_SEPARATOR = '`';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\TreePath(separator="`")
     * @ORM\Column(name="path", type="string", length=3000, nullable=true)
     */
    protected $path;

    /**
     * @Gedmo\TreePathSource
     * @ORM\Column(type="string", length=255, name="name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Note : Fetch "eager" option is required because on name update, in order to build
     * the new path, the materialized path extension uses the reflection api to retrieve
     * the instance's parents paths, but the parents are proxies and their "path" property
     * is empty until it is lazy-loaded via the dedicated getter...
     * (see Gedmo\Tree\Strategy\AbstractMaterializedPath, line 283 :
     *  '$pathProp->getValue($parent)' returns null).
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance",
     *      inversedBy="children",
     *      fetch="EAGER"
     * )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $parent;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\User",
     *      inversedBy="resourceInstances"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *      inversedBy="resourceInstances", cascade={"detach"}
     * )
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    protected $abstractResource;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance",
     *      mappedBy="parent"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *      inversedBy="resourcesInstance"
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * Returns the resource instance id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the resource instance id.
     *
     * @param type $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the resource instance creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->created;
    }

    /**
     * Returns the resource instance modification date.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->updated;
    }

    /**
     * Returns the resource instance creator.
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->user;
    }

    /**
     * Sets the resource instance creator.
     *
     * @param \Claroline\CoreBundle\Entity\User
     */
    public function setCreator(User $user)
    {
        $this->user = $user;
    }

    /**
     * Sets the parent resource instance.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceInstance $parent
     */
    public function setParent(ResourceInstance $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent resource instance.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceInstance
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the children resource instances.
     *
     * @return \Doctrine\Common\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Adds a child resource instance.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceInstance $resource
     */
    public function addChild(ResourceInstance $resource)
    {
        $this->children[] = $resource;
    }

    /**
     * Sets the workspace containing the resource instance.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     */
    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Returns the workspace containing the resource instance.
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Sets the resource the instance is referring to.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $abstractResource
     */
    public function setResource(AbstractResource $abstractResource)
    {
        $this->abstractResource = $abstractResource;
    }

    /**
     * Returns the resource the instance is referring to.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function getResource()
    {
        return $this->abstractResource;
    }

    /**
     * Returns the instance resource type (shortcut to the original resource type).
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function getResourceType()
    {
        return $this->abstractResource->getResourceType();
    }

    /**
     * Return the lvl value of the instance in the tree.
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Returns the "raw" path of the instance
     * (the path merge names and ids of all items).
     * Eg.: "Root-1/subdir-2/file.txt-3/"
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the path cleaned from its ids.
     * Eg.: "Root/subdir/file.txt"
     * @return
     */
    public function getPathForDisplay()
    {
        return self::convertPathForDisplay($this->path);
    }

    /**
     * Sets the resource name.
     *
     * @param string $name
     * @throws an exception if the name contains the path separator ('/').
     */
    public function setName($name)
    {
        if (strpos(self::PATH_SEPARATOR, $name) !== false) {
            throw new \InvalidArgumentException('Invalid character "' . self::PATH_SEPARATOR . '" in resource name.');
        }

        $this->name = $name;
    }

    /**
     * Returns the resource name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Convert a path for display: remove ids.
     * @param type $path
     * @return string
     */
    public static function convertPathForDisplay($path)
    {
        $pathForDisplay = preg_replace('(-\d+' . ResourceInstance::PATH_SEPARATOR . ')', '/', $path);
        if ($pathForDisplay !== null && strlen($pathForDisplay) > 0) {
            $pathForDisplay = substr_replace($pathForDisplay, "", -1);
        }
        return $pathForDisplay;
    }
}