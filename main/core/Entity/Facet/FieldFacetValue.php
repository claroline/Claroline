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
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

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
     * @Groups({"api_user"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stringValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $floatValue;

    /**
     * @ORM\Column(type="datetime", nullable=true)
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

    /**
     * @Groups({"api_user"})
     * @Accessor(getter="getValue") 
     */
    protected $value;

    public function setDateValue(\DateTime $dateValue)
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

    public function getValue()
    {
        switch ($this->getFieldFacet()->getType()) {
            case FieldFacet::FLOAT_TYPE: return $this->getFloatValue();
            case FieldFacet::DATE_TYPE: return $this->getDateValue();
            case FieldFacet::STRING_TYPE: return $this->getStringValue();
            default: return "error";
        }
    }
} 