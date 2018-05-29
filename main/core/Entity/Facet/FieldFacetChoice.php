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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_field_facet_choice")
 */
class FieldFacetChoice
{
    use Id;
    use Uuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     inversedBy="fieldFacetChoices"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var FieldFacet
     */
    private $fieldFacet;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $position;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     *
     * @var FieldFacetChoice
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     mappedBy="parent",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var ArrayCollection
     */
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

    /**
     * @param FieldFacet $ff
     */
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

    /**
     * @param FieldFacetChoice|null $parent
     */
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
        return $this->children->clear();
    }

    /**
     * @param FieldFacetChoice $child
     */
    public function addChild(FieldFacetChoice $child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
        }
    }

    /**
     * @param FieldFacetChoice $child
     */
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
