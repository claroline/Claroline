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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ClacoFormBundle\Repository\FieldValueRepository")
 * @ORM\Table(
 *     name="claro_clacoformbundle_field_value",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="field_unique_name", columns={"entry_id", "field_id"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"entry", "field"})
 */
class FieldValue
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Entry",
     *     inversedBy="fieldValues",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="entry_id", onDelete="CASCADE")
     *
     * @var Entry
     */
    protected $entry;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\ClacoFormBundle\Entity\Field")
     * @ORM\JoinColumn(name="field_id", onDelete="CASCADE")
     *
     * @var Field
     */
    protected $field;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetValue", cascade={"persist"})
     * @ORM\JoinColumn(name="field_facet_value_id", onDelete="CASCADE")
     *
     * @var FieldFacetValue
     */
    protected $fieldFacetValue;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setField(Field $field)
    {
        $this->field = $field;
    }

    /**
     * @return FieldFacetValue
     */
    public function getFieldFacetValue()
    {
        return $this->fieldFacetValue;
    }

    public function setFieldFacetValue(FieldFacetValue $fieldFacetValue)
    {
        return $this->fieldFacetValue = $fieldFacetValue;
    }

    public function getValue()
    {
        return !empty($this->fieldFacetValue) ? $this->fieldFacetValue->getValue() : null;
    }

    public function setValue($value)
    {
        if (!empty($this->fieldFacetValue)) {
            $this->fieldFacetValue->setValue($value);
        }
    }
}
