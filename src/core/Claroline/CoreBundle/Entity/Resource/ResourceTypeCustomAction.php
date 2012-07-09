<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_type_custom_action")
 */
class ResourceTypeCustomAction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="customActions", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    private $resourceType;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}