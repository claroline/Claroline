<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_mask_decoder")
 */
class MaskDecoder
{
    //this must be coherent with the MaskManager default array
    //@todo: unify this with the MaskManager
    const OPEN = 1;
    const COPY = 2;
    const EXPORT = 4;
    const DELETE = 8;
    const EDIT = 16;
    const ADMINISTRATE = 32;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $value;

    /**
     * @ORM\Column()
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="maskDecoders",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_type_id", onDelete="CASCADE", nullable=false)
     */
    protected $resourceType;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $position
     *
     * @return MaskDecoder
     */
    public function setValue($position)
    {
        $this->value = $position;

        return $this;
    }
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $name
     *
     * @return MaskDecoder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param ResourceType $resourceType
     *
     * @return MaskDecoder
     */
    public function setResourceType(ResourceType $resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }
}
