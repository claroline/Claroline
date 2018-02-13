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
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\FacetRepository")
 * @ORM\Table(name="claro_facet")
 * @UniqueEntity("name")
 */
class Facet
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", name="position")
     *
     * @var int
     */
    protected $position;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *     mappedBy="facet",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var ArrayCollection|PanelFacet[]
     */
    protected $panelFacets;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="facets"
     * )
     * @ORM\JoinTable(name="claro_facet_role")
     *
     * @var ArrayCollection|Role[]
     */
    protected $roles;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $forceCreationForm = false;

    /**
     * @ORM\Column(name="isMain", type="boolean")
     *
     * @var bool
     */
    protected $main = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->panelFacets = new ArrayCollection();
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param PanelFacet $panelFacet
     */
    public function addPanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacets->add($panelFacet);
    }

    /**
     * @param PanelFacet $panelFacet
     */
    public function removePanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacets->removeElement($panelFacet);
    }

    /**
     * @return ArrayCollection|PanelFacet[]
     */
    public function getPanelFacets()
    {
        return $this->panelFacets;
    }

    /**
     * Removes all PanelFacet.
     */
    public function resetPanelFacets()
    {
        foreach ($this->panelFacets as $panelFacet) {
            $panelFacet->setFacet(null);
        }

        $this->panelFacets = new ArrayCollection();
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
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $this->roles->add($role);
    }

    /**
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param bool $forceCreationForm
     */
    public function setForceCreationForm($forceCreationForm)
    {
        $this->forceCreationForm = $forceCreationForm;
    }

    /**
     * @return bool
     */
    public function getForceCreationForm()
    {
        return $this->forceCreationForm;
    }

    /**
     * @param bool|string $boolean
     *
     * @deprecated
     */
    public function setIsMain($boolean)
    {
        $this->setMain(
            !is_bool($boolean) ? $boolean === 'true' : $boolean
        );
    }

    /**
     * @param bool $main
     */
    public function setMain($main)
    {
        $this->main = $main;
    }

    /**
     * @return bool
     */
    public function isMain()
    {
        return $this->main;
    }
}
