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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\PanelFacetRepository")
 * @ORM\Table(name="claro_panel_facet")
 */
class PanelFacet
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\Facet",
     *      inversedBy="panelFacets"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    protected $facet;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     mappedBy="panelFacet",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"api_facet_admin", "api_profile"})
     * @SerializedName("fields")
     */
    protected $fieldsFacet;

    /**
     * @ORM\Column(type="integer", name="position")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $position;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $isDefaultCollapsed = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $isEditable = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacetRole",
     *     mappedBy="panelFacet"
     * )
     * @Groups({"api_facet_admin"})
     */
    protected $panelFacetsRole;

    public function __construct()
    {
        $this->fieldsFacet = new ArrayCollection();
        $this->panelFacetsRole = new ArrayCollection();
    }

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

    public function setFacet($facet)
    {
        $this->facet = $facet;
        $facet->addPanelFacet($this);
    }

    public function getFacet()
    {
        return $this->facet;
    }

    public function getFieldsFacet()
    {
        return $this->fieldsFacet;
    }

    public function addFieldFacet(FieldFacet $fieldFacet)
    {
        $this->fieldsFacet->add($fieldFacet);
    }

    public function addPanelFacetRole(PanelFacetRole $pfr)
    {
        $this->panelFacetsRole->add($pfr);
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setIsDefaultCollapsed($boolean)
    {
        $this->isDefaultCollapsed = $boolean;
    }

    public function getIsDefaultCollapsed()
    {
        return $this->isDefaultCollapsed;
    }

    public function isDefaultCollapsed()
    {
        return $this->isDefaultCollapsed;
    }

    public function isEditable()
    {
        return $this->isEditable;
    }

    public function setIsEditable($isEditable)
    {
        $this->isEditable = $isEditable;
    }

    public function isCollapsed()
    {
        return $this->isDefaultCollapsed ? 'true' : 'false';
    }

    public function getPanelFacetsRole()
    {
        return $this->panelFacetsRole;
    }
}
