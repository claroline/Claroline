<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_meta_type")
 */
class MetaType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

    /*
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\ResourceType",
     *      inversedBy="metaTypes"
     * )
     * @ORM\JoinTable(name="claro_meta_type_resource_type",
     *      joinColumns={@ORM\JoinColumn(name="meta_type_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")}
     * )
     */
    protected $resourceTypes;

    /**
     * Returns the meta type id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the meta type name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the meta type name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}