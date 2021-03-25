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
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\FacetRepository")
 * @ORM\Table(name="claro_facet")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Facet
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(unique=true)
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
     *     cascade={"all"}
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var ArrayCollection|PanelFacet[]
     */
    protected $panelFacets;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
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

    public function addPanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacets->add($panelFacet);
    }

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

    public function addRole(Role $role)
    {
        $this->roles->add($role);
    }

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
            !is_bool($boolean) ? 'true' === $boolean : $boolean
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
