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

use Claroline\CoreBundle\Entity\Facet\AbstractFacetValue;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_field_choice_category')]
#[ORM\Entity]
class FieldChoiceCategory extends AbstractFacetValue
{
    #[ORM\JoinColumn(name: 'field_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Field::class, inversedBy: 'fieldChoiceCategories')]
    protected ?Field $field = null;

    #[ORM\JoinColumn(name: 'category_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Category::class)]
    protected ?Category $category = null;

    #[ORM\JoinColumn(name: 'field_facet_choice_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: FieldFacetChoice::class)]
    protected ?FieldFacetChoice $fieldFacetChoice = null;

    public function getType(): string
    {
        return $this->field->getType();
    }

    public function getField(): ?Field
    {
        return $this->field;
    }

    public function setField(Field $field)
    {
        $this->field = $field;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getFieldFacetChoice(): ?FieldFacetChoice
    {
        return $this->fieldFacetChoice;
    }

    public function setFieldFacetChoice(?FieldFacetChoice $fieldFacetChoice = null)
    {
        $this->fieldFacetChoice = $fieldFacetChoice;
    }
}
