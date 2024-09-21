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

use Claroline\ClacoFormBundle\Repository\FieldValueRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_field_value')]
#[ORM\UniqueConstraint(name: 'field_unique_name', columns: ['entry_id', 'field_id'])]
#[ORM\Entity(repositoryClass: FieldValueRepository::class)]
class FieldValue
{
    use Id;
    use Uuid;

    /**
     *
     * @var Entry
     */
    #[ORM\JoinColumn(name: 'entry_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Entry::class, inversedBy: 'fieldValues', cascade: ['persist'])]
    protected $entry;

    /**
     *
     * @var Field
     */
    #[ORM\JoinColumn(name: 'field_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Field::class)]
    protected $field;

    /**
     *
     * @var FieldFacetValue
     */
    #[ORM\JoinColumn(name: 'field_facet_value_id', onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: FieldFacetValue::class, cascade: ['persist'])]
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
