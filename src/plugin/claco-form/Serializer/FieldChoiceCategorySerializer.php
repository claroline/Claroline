<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\ClacoFormBundle\Repository\CategoryRepository;
use Claroline\ClacoFormBundle\Repository\FieldRepository;
use Claroline\CoreBundle\Manager\FacetManager;

class FieldChoiceCategorySerializer
{
    use SerializerTrait;

    private FieldSerializer $fieldSerializer;
    private FacetManager $facetManager;

    private CategoryRepository $categoryRepo;
    private FieldRepository $fieldRepo;

    public function __construct(
        FieldSerializer $fieldSerializer,
        ObjectManager $om,
        FacetManager $facetManager
    ) {
        $this->fieldSerializer = $fieldSerializer;
        $this->facetManager = $facetManager;

        $this->categoryRepo = $om->getRepository(Category::class);
        $this->fieldRepo = $om->getRepository(Field::class);
    }

    public function getName(): string
    {
        return 'clacoform_field_choice_category';
    }

    public function serialize(FieldChoiceCategory $fieldChoiceCategory): array
    {
        return [
            'id' => $fieldChoiceCategory->getUuid(),
            'field' => $this->fieldSerializer->serialize($fieldChoiceCategory->getField(), [Options::SERIALIZE_MINIMAL]),
            'category' => [
                'id' => $fieldChoiceCategory->getCategory()->getUuid(),
            ],
            'value' => $this->facetManager->serializeFieldValue(
                $fieldChoiceCategory,
                $fieldChoiceCategory->getType(),
                $fieldChoiceCategory->getValue()
            ),
        ];
    }

    public function deserialize(array $data, FieldChoiceCategory $fieldChoiceCategory): FieldChoiceCategory
    {
        if (isset($data['category']['id'])) {
            $category = $this->categoryRepo->findOneBy(['uuid' => $data['category']['id']]);

            if (!empty($category)) {
                $fieldChoiceCategory->setCategory($category);
            }
        }

        $field = isset($data['field']['id']) ?
            $this->fieldRepo->findByFieldFacetUuid($data['field']['id']) :
            null;

        if (!empty($field)) {
            $fieldChoiceCategory->setField($field);

            $fieldChoiceCategory->setValue(
                $this->facetManager->deserializeFieldValue(
                    $fieldChoiceCategory,
                    $fieldChoiceCategory->getType(),
                    $data['value']
                )
            );
        }

        return $fieldChoiceCategory;
    }
}
