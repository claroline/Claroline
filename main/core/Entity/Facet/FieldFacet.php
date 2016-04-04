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
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\FieldFacetRepository")
 * @ORM\Table(name="claro_field_facet")
 */
class FieldFacet
{
    const STRING_TYPE = 1;
    const FLOAT_TYPE = 2;
    const DATE_TYPE = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"admin"})
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"admin"})
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *      inversedBy="fieldsFacet"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $panelFacet;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetValue",
     *     mappedBy="fieldFacet",
     *     cascade={"persist"}
     * )
     */
    protected $fieldsFacetValue;

    /**
     * @ORM\Column(type="integer", name="position")
     */
    protected $position;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetRole",
     *     mappedBy="fieldFacet"
     * )
     */
    protected $fieldFacetsRole;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isVisibleByOwner = true;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isEditableByOwner = false;

    public function __construct()
    {
        $this->fieldsFacetValue = new ArrayCollection();
        $this->fieldFacetsRole  = new ArrayCollection();
    }

    /**
     * @param Facet $facet
     */
    public function setPanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacet = $panelFacet;
    }

    /**
     * @return Facet
     */
    public function getPanelFacet()
    {
        return $this->panelFacet;
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

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFieldsFacetValue()
    {
        return $this->fieldsFacetValue;
    }

    public function addFieldFacet(FieldFacetValue $fieldFacetValue)
    {
        $this->fieldsFacetValue->add($fieldFacetValue);
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getTypeTranslationKey()
    {
         switch ($this->type) {
            case self::FLOAT_TYPE: return "number";
            case self::DATE_TYPE: return "date";
            case self::STRING_TYPE: return "text";
            default: return "error";
        }
    }

    public function getInputType()
    {
        switch ($this->type) {
            case self::FLOAT_TYPE: return "number";
            case self::DATE_TYPE: return "date";
            case self::STRING_TYPE: return "text";
            default: return "error";
        }
    }

    public function getFieldFacetsRole()
    {
        return $this->fieldFacetsRole;
    }

    public function setIsVisibleByOwner($boolean)
    {
        $this->isVisibleByOwner = $boolean;
    }

    public function getIsVisibleByOwner()
    {
        return $this->isVisibleByOwner;
    }

    public function setIsEditableByOwner($boolean)
    {
        $this->isEditableByOwner = $boolean;
    }

    public function getIsEditableByOwner()
    {
        return $this->isEditableByOwner;
    }
}
