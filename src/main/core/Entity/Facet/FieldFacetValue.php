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
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Facet\FieldFacetValueRepository")
 * @ORM\Table(name="claro_field_facet_value")
 */
class FieldFacetValue
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $stringValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @var float
     */
    protected $floatValue;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $dateValue;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @var array
     */
    protected $arrayValue;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $boolValue;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="fieldsFacetValue",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var FieldFacet
     */
    protected $fieldFacet;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setDateValue(\DateTime $dateValue = null)
    {
        $this->dateValue = $dateValue;
    }

    /**
     * @param bool $format
     *
     * @return \DateTime|string
     */
    public function getDateValue($format = true)
    {
        if ($format) { // TODO : this should not be done here
            return !empty($this->dateValue) ? $this->dateValue->format('Y-m-d\TH:i:s') : null;
        }

        return $this->dateValue;
    }

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

    /**
     * @param float $floatValue
     */
    public function setFloatValue($floatValue)
    {
        $this->floatValue = $floatValue;
    }

    /**
     * @return float
     */
    public function getFloatValue()
    {
        return $this->floatValue;
    }

    /**
     * @param string $stringValue
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;
    }

    /**
     * @return string
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    public function setArrayValue(array $arrayValue = null)
    {
        $this->arrayValue = $arrayValue;
    }

    /**
     * @return array
     */
    public function getArrayValue()
    {
        return $this->arrayValue;
    }

    /**
     * @param bool $boolValue
     */
    public function setBoolValue($boolValue)
    {
        $this->boolValue = $boolValue;
    }

    /**
     * @return bool
     */
    public function getBoolValue()
    {
        return $this->boolValue;
    }

    public function setUser(User $user = null)
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

    /**
     * @return mixed
     */
    public function getValue()
    {
        switch ($this->getFieldFacet()->getType()) {
            case FieldFacet::NUMBER_TYPE: return $this->getFloatValue();
            case FieldFacet::DATE_TYPE: return $this->getDateValue();
            case FieldFacet::CASCADE_TYPE: return $this->getArrayValue();
            case FieldFacet::FILE_TYPE: return $this->getArrayValue();
            case FieldFacet::BOOLEAN_TYPE: return $this->getBoolValue();
            case FieldFacet::CHOICE_TYPE:
                $options = $this->getFieldFacet()->getOptions();

                return isset($options['multiple']) && $options['multiple'] ?
                    $this->getArrayValue() :
                    $this->getStringValue();
            default: return $this->getStringValue();
        }
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        switch ($this->getFieldFacet()->getType()) {
            case FieldFacet::NUMBER_TYPE:
                $this->setFloatValue($value);
                break;
            case FieldFacet::DATE_TYPE:
                if ($value) {
                    $dateValue = new \DateTime($value);
                    $this->setDateValue($dateValue);
                } else {
                    $this->setDateValue(null);
                }
                break;
            case FieldFacet::CASCADE_TYPE:
            case FieldFacet::FILE_TYPE:
                $this->setArrayValue($value);
                break;
            case FieldFacet::BOOLEAN_TYPE:
                $this->setBoolValue($value);
                break;
            case FieldFacet::CHOICE_TYPE:
                $options = $this->getFieldFacet()->getOptions();

                if (isset($options['multiple']) && $options['multiple']) {
                    $this->setArrayValue($value);
                } else {
                    $this->setStringValue($value);
                }
                break;
            default:
                $this->setStringValue($value);
        }
    }
}
