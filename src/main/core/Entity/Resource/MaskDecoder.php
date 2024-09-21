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

use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Repository\Resource\ResourceMaskDecoderRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

#[ORM\Table(name: 'claro_resource_mask_decoder')]
#[Index(name: 'value', columns: ['value'])]
#[Index(name: 'name', columns: ['name'])]
#[ORM\Entity(repositoryClass: ResourceMaskDecoderRepository::class)]
class MaskDecoder
{
    use Id;

    //this must be coherent with the MaskManager default array
    //@todo: unify this with the MaskManager
    const OPEN = 1;
    const COPY = 2;
    const EXPORT = 4;
    const DELETE = 8;
    const EDIT = 16;
    const ADMINISTRATE = 32;

    #[ORM\Column(type: Types::INTEGER)]
    protected $value;

    #[ORM\Column]
    protected $name;

    #[ORM\JoinColumn(name: 'resource_type_id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\ManyToOne(targetEntity: ResourceType::class, inversedBy: 'maskDecoders', cascade: ['persist'])]
    protected $resourceType;

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

    public function setResourceType(ResourceType $resourceType)
    {
        if ($this->resourceType instanceof ResourceType) {
            $this->resourceType->removeMaskDecoder($this);
        }

        $this->resourceType = $resourceType;
        $this->resourceType->addMaskDecoder($this);

        return $this;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }
}
