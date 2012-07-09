<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\License;

/**
 * Base entity for all resources.
 *
 * @ORM\Entity
 * @ORM\Table(name="claro_resource")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "file" = "File",
 *     "directory" = "Directory",
 *     "link" = "Link",
 *     "text" = "Text"
 * })
 */
abstract class AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, name="name")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance", mappedBy="abstractResource", cascade={"persist", "remove"})
     */
    private $resourceInstances;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\License", inversedBy="abstractResources", cascade={"persist"})
     * @ORM\JoinColumn(name="license_id", referencedColumnName="id")
     */
    private $license;

    /**
     * @ORM\Column(type="integer", name="share_type")
     */
    private $shareType;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="abstractResource", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    private $resourceType;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\user", inversedBy="abstractResource", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255, name="mime_type")
     */
    private $mimeType;

    const PRIVATE_RESOURCE = 0;
    const PUBLIC_RESOURCE = 1;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setShareType(self::PRIVATE_RESOURCE);
        $this->resourceInstances = new ArrayCollection();
    }

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
     * Sets the resource name.
     *
     * @param string $name
     */
    public function setName($name)
    {
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
     * Adds a resource instance to the instance collection.
     *
     * @param ResourceInstance $resourceInstance
     */
    public function addResourceInstance(ResourceInstance $resourceInstance)
    {
        $this->resourceInstances->add($resourceInstance);
    }

    /**
     * Removes a resource instance from the instance collection.
     *
     * @param ResourceInstance $resourceInstance
     */
    public function removeResourceInstance(ResourceInstance $resourceInstance)
    {
        $this->resourceInstances->removeElement($resourceInstance);
    }

    /**
     * Returns the number of instances of the resource.
     *
     * @return integer
     */
    public function getInstanceCount()
    {
        return count($this->resourceInstances);
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
     * Gets the share type
     *
     * @return integer
     */
    public function getShareType()
    {
        return $this->shareType;
    }

    /**
     * Sets the share type
     *
     * @param integer $shareType
     */
    public function setShareType($shareType)
    {
        $this->shareType=$shareType;
    }

    /**
     * Returns the resource creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->created;
    }

    /**
     * Returns the resource modification date.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->updated;
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
     * Sets the resource mime type.
     *
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Returns the resource mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function getResourceInstances()
    {
        return $this->resourceInstances;
    }
}