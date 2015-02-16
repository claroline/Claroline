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
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Claroline\CoreBundle\Entity\Role;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\PanelFacetRepository")
 * @ORM\Table(name="claro_panel_facet")
 */
class PanelFacet {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Facet\Facet",
     *      inversedBy="panelFacets"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $facet;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     mappedBy="panelFacet",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $fieldsFacet;

    /**
     * @ORM\Column(type="integer", name="position")
     */
    protected $position;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isDefaultCollapsed = false;

    public function __construct()
    {
        $this->fieldsFacet = new ArrayCollection();
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

    public function isCollapsed()
    {
        return $this->isDefaultCollapsed ? 'true': 'false';
    }
}
