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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_field_facet_choice")
 */
class FieldFacetChoice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    private $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"api_facet_admin", "api_profile"})
     * @SerializedName("label")
     */
    private $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     inversedBy="fieldFacetChoices"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $fieldFacet;

    /**
     * @ORM\Column(type="integer", name="position")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $position;

    /**
     * @Groups({"api_profile"})
     * @Accessor(getter="getValue")
     */
    protected $value;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups({"api_facet_admin"})
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice",
     *     mappedBy="parent"
     * )
     */
    protected $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setLabel($label)
    {
        $this->name = $label;
    }

    public function getLabel()
    {
        return $this->name;
    }

    public function setFieldFacet(FieldFacet $ff)
    {
        $this->fieldFacet = $ff;
        $ff->addFieldChoice($this);
    }

    public function getFieldFacet()
    {
        return $this->fieldFacet;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(FieldFacetChoice $parent = null)
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->children->toArray();
    }

    //for the api form select field.
    public function getValue()
    {
        return $this->name;
    }
}
