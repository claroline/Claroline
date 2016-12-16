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

use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity()
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
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ClacoFormBundle\Entity\Entry",
     *     inversedBy="fieldValues"
     * )
     * @ORM\JoinColumn(name="entry_id", onDelete="CASCADE")
     * @Groups({"api_claco_form"})
     * @SerializedName("entry")
     */
    protected $entry;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\ClacoFormBundle\Entity\Field")
     * @ORM\JoinColumn(name="field_id", onDelete="CASCADE")
     * @Groups({"api_claco_form", "api_user_min"})
     * @SerializedName("field")
     */
    protected $field;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetValue")
     * @ORM\JoinColumn(name="field_facet_value_id", onDelete="CASCADE")
     * @Groups({"api_user_min"})
     * @SerializedName("fieldFacetValue")
     */
    protected $fieldFacetValue;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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

    public function getFieldFacetValue()
    {
        return $this->fieldFacetValue;
    }

    public function setFieldFacetValue(FieldFacetValue $fieldFacetValue)
    {
        return $this->fieldFacetValue = $fieldFacetValue;
    }
}
