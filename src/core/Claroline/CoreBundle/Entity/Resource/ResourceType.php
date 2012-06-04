<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Plugin;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceTypeRepository")
 * @ORM\Table(name="claro_resource_type")
 */
class ResourceType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */    
    private $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private $type; 
    
    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource", mappedBy="resourceType", cascade={"persist"})
     */ 
    private $abstractResource;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    
    private $class;
    
    /**
     * @ORM\Column(type="boolean", name="is_navigable")
     */
    private $isNavigable;
    
    /**
     * @ORM\Column(type="boolean", name="is_listable")
     */
    private $isListable;
    
    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    private $plugin;
    
    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\MetaType",
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_meta_type_resource_type",
     *      joinColumns={@ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="meta_type_id", referencedColumnName="id")}
     * )
     */
    protected $metaTypes;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", mappedBy="parent")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $children;
    
    public function __construct()
    {
        $this->resourcesInstance = new ArrayCollection();
        $this->metaTypes = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type=$type;
    }
        
    public function getResources()
    {
        return $this->resourcesInstance;
    }
    
    public function addResource(ResourceInstance $resourceInstance)
    {
        $this->resourcesInstance[] = $resourceInstance;
        $resourceInstance->setUser($this);
    }
         
    
    public function setNavigable($isNavigable)
    {
        $this->isNavigable=$isNavigable;
    }
    
    public function getNavigable()
    {
        return $this->isNavigable;
    }
    
    public function setListable($isListable)
    {
        $this->isListable=$isListable;
    }
    
    public function getListable()
    {
        return $this->isListable;
    }
    
    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
    
    public function getPlugin()
    {
        return $this->plugin;
    }
    
    public function getClass()
    {
        return $this->class;
    }
    
    public function setClass($class)
    {
        $this->class=$class;
    }
    
    public function addMetaType($metaType)
    {
        $this->metaTypes->add($metaType);
    }
    
    public function removeMetaType($metaType)
    {
        $this->metaTypes->removeElement($metaType);
    }
    
    public function getMetaTypes()
    {
        return $this->metaTypes;
    }
    
    public function setParent(ResourceType $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }
    
    public function getResource()
    {
        return $this->abstractResource;
    }
    
    public function setResource($abstractResource)
    {
        $this->abstractResource = $abstractResource;
    }
}