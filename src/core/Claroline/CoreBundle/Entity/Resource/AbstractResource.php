<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\License;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Base entity for all resources.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\AbstractResourceRepository")
 * @ORM\Table(name="claro_resource")
 * @ORM\InheritanceType("JOINED")
 * @Gedmo\Tree(type="materializedPath")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "file" = "File",
 *     "directory" = "Directory",
 *     "link" = "Link",
 *     "text" = "Text",
 *     "resource_shortcut" = "ResourceShortcut",
 *     "activity" = "Activity"
 * })
 */
abstract class AbstractResource
{
    const PATH_SEPARATOR = '`';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\License",
     *     inversedBy="abstractResources",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="license_id", referencedColumnName="id")
     */
    protected $license;

    /**
     * @ORM\Column(type="datetime", name="creation_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="datetime", name="modification_date")
     * @Gedmo\Timestampable(on="update")
     */
    protected $modificationDate;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="abstractResources",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    protected $resourceType;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="abstractResources",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $creator;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="icon_id", referencedColumnName="id")
     */
    protected $icon;

    /**
     * @Gedmo\TreePathSource
     * @ORM\Column(type="string", length=255, name="name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Note : Fetch "eager" option is required because on name update, in order to build
     * the new path, the materialized path extension uses the reflection api to retrieve
     * the resource's parents paths, but the parents are proxies and their "path" property
     * is empty until it is lazy-loaded via the dedicated getter...
     * (see Gedmo\Tree\Strategy\AbstractMaterializedPath, line 283 :
     *  '$pathProp->getValue($parent)' returns null).
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *     inversedBy="children",
     *     fetch="EAGER"
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
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *     mappedBy="parent"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceShortcut",
     *     mappedBy="resource",
     *     cascade={"remove"}
     * )
     */
    protected $shortcuts;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *      inversedBy="resources"
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * @Gedmo\TreePath(separator="`")
     * @ORM\Column(name="path", type="string", length=3000, nullable=true)
     */
    protected $path;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="resource"
     * )
     */
    protected $rights;

    //The user icon.
    //Used by some forms.
    protected $userIcon;


    /**
     * Returns the resource id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the resource id.
     * Required by the ResourceController when it creates a fictionnal root
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the resource license.
     *
     * @return \Claroline\CoreBundle\Entity\License
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Sets the resource license.
     *
     * @param \Claroline\CoreBundle\Entity\License
     */
    public function setLicense(License $license)
    {
        $this->license = $license;
    }

    /**
     * Returns the resource creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Returns the resource modification date.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Returns the resource type.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Sets the resource type.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * Returns the resource creator.
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Sets the resource creator.
     *
     * @param \Claroline\CoreBundle\Entity\User
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
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
     * Adds a child resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     */
    public function addChild(AbstractResource $resource)
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

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Sets the parent resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $parent
     */
    public function setParent(AbstractResource $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent resource.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return the lvl value of the resource in the tree.
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Returns the "raw" path of the resource
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
            throw new \InvalidArgumentException(
                'Invalid character "' . self::PATH_SEPARATOR . '" in resource name.'
            );
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
     * Sets a user icon.
     * @param file $iconFile
     */
    public function setUserIcon($userIcon)
    {
        $this->userIcon = $userIcon;
    }

    /**
     * Gets the user icon.
     *
     * @return file
     */
    public function getUserIcon()
    {
        return $this->userIcon;
    }

    /**
     * Convert a path for display: remove ids.
     *
     * @param string $path
     *
     * @return string
     */
    public static function convertPathForDisplay($path)
    {
        $pathForDisplay = preg_replace('/-\d+' . self::PATH_SEPARATOR . '/', ' / ', $path);

        if ($pathForDisplay !== null && strlen($pathForDisplay) > 0) {
            $pathForDisplay = substr_replace($pathForDisplay, "", -3);
        }

        return $pathForDisplay;
    }

    public function getShortcuts()
    {
        return $this->shortcuts;
    }

    public function getRights()
    {
        return $this->rights;
    }
}