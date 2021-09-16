<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_clacoformbundle_field_choice_category")
 */
class FieldChoiceCategory
{
    use Uuid;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Field",
     *     inversedBy="fieldChoiceCategories"
     * )
     * @ORM\JoinColumn(name="field_id", nullable=false, onDelete="CASCADE")
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\ClacoFormBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", nullable=false, onDelete="CASCADE")
     */
    protected $category;

    /**
     * @ORM\Column(name="field_value", nullable=true)
     */
    protected $stringValue;

    /**
     * @ORM\Column(name="float_value", type="float", nullable=true)
     *
     * @var float
     */
    protected $floatValue;

    /**
     * @ORM\Column(name="date_value", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $dateValue;

    /**
     * @ORM\Column(name="array_value", type="json_array", nullable=true)
     *
     * @var array
     */
    protected $arrayValue;

    /**
     * @ORM\Column(name="bool_value", type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $boolValue;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetChoice")
     * @ORM\JoinColumn(name="field_facet_choice_id", nullable=true, onDelete="CASCADE")
     */
    protected $fieldFacetChoice;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setField(Field $field)
    {
        $this->field = $field;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getStringValue()
    {
        return $this->stringValue;
    }

    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;
    }

    public function getDateValue($format = true)
    {
        if ($format) {
            return !empty($this->dateValue) ? $this->dateValue->format('Y-m-d\TH:i:s') : null;
        }

        return $this->dateValue;
    }

    public function setDateValue(\DateTime $dateValue = null)
    {
        $this->dateValue = $dateValue;
    }

    public function getFloatValue()
    {
        return $this->floatValue;
    }

    public function setFloatValue($floatValue)
    {
        $this->floatValue = $floatValue;
    }

    public function getArrayValue()
    {
        return $this->arrayValue;
    }

    public function setArrayValue(array $arrayValue = null)
    {
        $this->arrayValue = $arrayValue;
    }

    public function getBoolValue()
    {
        return $this->boolValue;
    }

    public function setBoolValue($boolValue)
    {
        $this->boolValue = $boolValue;
    }

    public function getValue()
    {
        $value = null;

        switch ($this->field->getType()) {
            case FieldFacet::NUMBER_TYPE:
                $value = $this->getFloatValue();
                break;
            case FieldFacet::DATE_TYPE:
                $value = $this->getDateValue();
                break;
            case FieldFacet::CASCADE_TYPE:
            case FieldFacet::FILE_TYPE:
                $value = $this->getArrayValue();
                break;
            case FieldFacet::BOOLEAN_TYPE:
                $value = $this->getBoolValue();
                break;
            case FieldFacet::CHOICE_TYPE:
                $options = $this->field->getOptions();
                $value = isset($options['multiple']) && $options['multiple'] ?
                    $this->getArrayValue() :
                    $this->getStringValue();
                break;
            default:
                $value = $this->getStringValue();
        }

        return $value;
    }

    public function setValue($value)
    {
        switch ($this->field->getType()) {
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
                $options = $this->field->getOptions();

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

    public function getFieldFacetChoice()
    {
        return $this->fieldFacetChoice;
    }

    public function setFieldFacetChoice(FieldFacetChoice $fieldFacetChoice = null)
    {
        $this->fieldFacetChoice = $fieldFacetChoice;
    }
}
