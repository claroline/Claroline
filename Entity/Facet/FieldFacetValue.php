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

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_field_facet_value")
 */
class FieldFacetValue
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $stringValue;

    /**
     * @ORM\Column(type="float")
     */
    protected $floatValue;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dateValue;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="fieldsFacetValue",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     inversedBy="fieldsFacetValue",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $fieldFacet;

    public function setDateValue($dateValue)
    {
        $this->dateValue = $dateValue;
    }

    public function getDateValue()
    {
        return $this->dateValue;
    }

    /**
     * @param FieldFacet $fieldFacet
     */
    public function setFieldFacet(FieldFacet $fieldFacet)
    {
        $this->fieldFacet = $fieldFacet;
    }

    /**
     * @return FieldFacet
     */
    public function getFieldFacet()
    {
        return $this->fieldFacet;
    }

    public function setFloatValue($floatValue)
    {
        $this->floatValue = $floatValue;
    }

    public function getFloatValue()
    {
        return $this->floatValue;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;
    }

    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
} 