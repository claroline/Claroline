<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Facet;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_field_facet_choice')]
#[ORM\Entity]
class FieldFacetChoice
{
    use Id;
    use Uuid;

    /**
     * @var string
     */
    #[ORM\Column]
    private $name;

    /**
     *
     *
     * @var FieldFacet
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: FieldFacet::class, inversedBy: 'fieldFacetChoices')]
    private $fieldFacet;

    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER)]
    protected $position;

    /**
     *
     *
     * @var FieldFacetChoice
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: FieldFacetChoice::class, inversedBy: 'children')]
    protected $parent;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: FieldFacetChoice::class, cascade: ['persist', 'remove'])]
    protected $children;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->refreshUuid();
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->name = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->name;
    }

    public function setFieldFacet(FieldFacet $ff)
    {
        $this->fieldFacet = $ff;
        $ff->addFieldChoice($this);
    }

    /**
     * @return FieldFacet
     */
    public function getFieldFacet()
    {
        return $this->fieldFacet;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return FieldFacetChoice|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children->toArray();
    }

    public function emptyChildren()
    {
        $this->children->clear();
    }

    public function addChild(FieldFacetChoice $child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
        }
    }

    public function removeChild(FieldFacetChoice $child)
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
        }
    }

    /**
     * for the api form select field.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->name;
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
