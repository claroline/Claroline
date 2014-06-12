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

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_facet")
 */
class Facet {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", name="position")
     */
    protected $position;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     mappedBy="facet",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $fieldsFacet;

    public function __construct()
    {
        $this->fieldsFacet = new ArrayCollection();
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
} 