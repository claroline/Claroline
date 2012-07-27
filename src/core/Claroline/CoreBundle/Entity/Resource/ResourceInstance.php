<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceInstanceRepository")
 * @ORM\Table(name="claro_resource_instance")
 * @Gedmo\Tree(type="nested")
 */
class ResourceInstance
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

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
     *      inversedBy="resourceInstances"
     * )
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    protected $abstractResource;

    /**
     * @ORM\Column(name="lft", type="integer")
     * @Gedmo\TreeLeft
     */
    protected $lft;

    /**
     * @ORM\Column(name="lvl", type="integer")
     * @Gedmo\TreeLevel
     */
    protected $lvl;

    /**
     * @ORM\Column(name="rgt", type="integer")
     * @Gedmo\TreeRight
     */
    protected $rgt;

    /**
     * @ORM\Column(name="root", type="integer", nullable=true)
     * @Gedmo\TreeRoot
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance",
     *      inversedBy="children"
     * )
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

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
     * Returns the name of the resource the instance is referring to (shortcut).
     *
     * @return string
     */
    public function getName()
    {
        return $this->abstractResource->getName();
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
     * Returns the left value of the instance in the nested tree.
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Returns the right value of the instance in the nested tree.
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Returns the root value of the instance in the nested tree.
     *
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Returns the virtual pathname.
     */
    public function getPath()
    {
        $path='';

        if (null != $this->getParent()) {
            $path = $this->parent->getPath() . DIRECTORY_SEPARATOR . $this->getName();
        } else {
            $path = DIRECTORY_SEPARATOR . $this->getName();
        }

        return addslashes($path);
    }
}