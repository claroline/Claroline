<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="abstractResources", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    private $resourceType;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", inversedBy="abstractResources", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255, name="mime_type")
     */
    private $mimeType;

    /**
     * @Assert\NotBlank()
     */
    private $name;

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
     * Returns the resource share type. If the first parameter is set to true,
     * the type will be returned as a string, otherwise it will be returned
     * as an integer.
     *

     * @param boolean $asString
     *
     * @return integer|string
     */
    public function getShareType($asString = false)
    {
        if (true === $asString) {
            switch ($this->shareType) {
                case self::PRIVATE_RESOURCE:
                    return 'private';
                    break;
                case self::PUBLIC_RESOURCE:
                    return 'public';
                    break;
            }
        }

        return $this->shareType;
    }

    /**
     * Sets the share type
     *
     * @param integer $shareType
     */
    public function setShareType($shareType)
    {
        $this->shareType = $shareType;
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

    /**
     * Returns the instances of the resource.
     *
     * @return ArrayCollection[ResourceInstance]
     */
    public function getResourceInstances()
    {
        return $this->resourceInstances;
    }

    /**
     * Returns the resource name. Required for the FormType.
     *
     * @param type $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}