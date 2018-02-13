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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\PanelFacetRepository")
 * @ORM\Table(name="claro_panel_facet")
 */
class PanelFacet
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin", "api_profile"})
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"api_facet_admin", "api_profile"})
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\Facet",
     *      inversedBy="panelFacets"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var Facet
     */
    protected $facet;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     mappedBy="panelFacet",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"api_facet_admin", "api_profile"})
     * @SerializedName("fields")
     *
     * @var ArrayCollection
     */
    protected $fieldsFacet;

    /**
     * @ORM\Column(type="integer", name="position")
     * @Groups({"api_facet_admin", "api_profile"})
     *
     * @var int
     */
    protected $position;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin", "api_profile"})
     *
     * @var bool
     */
    protected $isDefaultCollapsed = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin", "api_profile"})
     *
     * @var bool
     */
    protected $isEditable = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacetRole",
     *     mappedBy="panelFacet"
     * )
     * @Groups({"api_facet_admin"})
     *
     * @var ArrayCollection
     */
    protected $panelFacetsRole;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fieldsFacet = new ArrayCollection();
        $this->panelFacetsRole = new ArrayCollection();
        $this->refreshUuid();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param Facet|null $facet
     */
    public function setFacet(Facet $facet = null)
    {
        $this->facet = $facet;

        if ($facet) {
            $facet->addPanelFacet($this);
        }
    }

    /**
     * @return Facet|null
     */
    public function getFacet()
    {
        return $this->facet;
    }

    /**
     * @return FieldFacet[]|ArrayCollection
     */
    public function getFieldsFacet()
    {
        return $this->fieldsFacet;
    }

    /**
     * @param FieldFacet $fieldFacet
     */
    public function addFieldFacet(FieldFacet $fieldFacet)
    {
        $this->fieldsFacet->add($fieldFacet);
    }

    /**
     * @param PanelFacetRole $pfr
     */
    public function addPanelFacetRole(PanelFacetRole $pfr)
    {
        $this->panelFacetsRole->add($pfr);
    }

    /**
     * Remove all field facets.
     */
    public function resetFieldFacets()
    {
        foreach ($this->fieldsFacet as $field) {
            $field->setPanelFacet(null);
        }

        $this->fieldsFacet = new ArrayCollection();
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
     * @param bool $boolean
     */
    public function setIsDefaultCollapsed($boolean)
    {
        $this->isDefaultCollapsed = $boolean;
    }

    /**
     * @return bool
     *
     * @deprecated
     */
    public function getIsDefaultCollapsed()
    {
        return $this->isDefaultCollapsed;
    }

    /**
     * @return bool
     */
    public function isDefaultCollapsed()
    {
        return $this->isDefaultCollapsed;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->isEditable;
    }

    /**
     * @param bool $isEditable
     */
    public function setIsEditable($isEditable)
    {
        $this->isEditable = $isEditable;
    }

    /**
     * @return string
     *
     * @deprecated
     */
    public function isCollapsed()
    {
        return $this->isDefaultCollapsed ? 'true' : 'false';
    }

    /**
     * @return ArrayCollection
     */
    public function getPanelFacetsRole()
    {
        return $this->panelFacetsRole;
    }
}
