<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"file" = "File", "directory" = "Directory", "link" = "Link", "text" = "Text"} )
 */
abstract class AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, name="name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance", mappedBy="abstractResource")
     */
    protected $resourceInstances;

    /**
     * @ORM\Column(type="integer", name="count_instance")
     */
    protected $instanceAmount;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\License", inversedBy="abstractResources", cascade={"persist"})
     * @ORM\JoinColumn(name="license_id", referencedColumnName="id")
     */
    protected $license;

    /**
     * @ORM\Column(type="boolean", name="is_sharable")
     */
    protected $isSharable;

    /**
    * @ORM\Column(type="datetime")
    * @Gedmo\Timestampable(on="create")
    */
    protected $created;

   /**
    * @ORM\Column(type="datetime")
    * @Gedmo\Timestampable(on="update")
    */
    protected $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="abstractResource", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    protected $resourceType;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\user", inversedBy="abstractResource", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    public function __construct()
    {
        $this->resourceInstances = new ArrayCollection();
        $this->instanceAmount = 0;
    }

    public function setId($id)
    {
        $this->id=$id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addResourceInstance(ResourceInstance $resourceInstance)
    {
        $this->resourceInstances->add($resourceInstance);
    }

    public function removeResourceInstance(ResourceInstance $resourceInstance)
    {
        $this->resourceInstances->removeElement($resourceInstance);
    }

    public function incrInstance()
    {
        $this->instanceAmount++;
    }

    public function decrInstance()
    {
        $this->instanceAmount--;
    }

    public function getInstanceAmount()
    {
        return $this->instanceAmount;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function isSharable()
    {
        return $this->isSharable;
    }

    public function setSharable($isSharable)
    {
        $this->isSharable=$isSharable;
    }

    public function getCreationDate()
    {
        return $this->created;
    }

    public function getModificationDate()
    {
        return $this->updated;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }
}