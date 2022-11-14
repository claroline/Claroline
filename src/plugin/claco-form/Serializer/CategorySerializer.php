<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;

class CategorySerializer
{
    use SerializerTrait;

    /** @var FieldChoiceCategorySerializer */
    private $fieldChoiceCategorySerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var ObjectManager */
    private $om;

    private $clacoFormRepo;
    private $fieldChoiceCategoryRepo;
    private $userRepo;

    /**
     * CategorySerializer constructor.
     */
    public function __construct(
        FieldChoiceCategorySerializer $fieldChoiceCategorySerializer,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->fieldChoiceCategorySerializer = $fieldChoiceCategorySerializer;
        $this->userSerializer = $userSerializer;
        $this->om = $om;

        $this->clacoFormRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
        $this->fieldChoiceCategoryRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\FieldChoiceCategory');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    public function getName()
    {
        return 'clacoform_category';
    }

    /**
     * Serializes a Category entity for the JSON api.
     *
     * @param Category $category - the category to serialize
     * @param array    $options  - a list of serialization options
     *
     * @return array - the serialized representation of the category
     */
    public function serialize(Category $category, array $options = [])
    {
        $serialized = [
            'id' => $category->getUuid(),
            'name' => $category->getName(),
            'details' => $category->getDetails(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'managers' => array_map(function (User $manager) {
                    return $this->userSerializer->serialize($manager, [Options::SERIALIZE_MINIMAL]);
                }, $category->getManagers()),
            ]);
            $serialized = array_merge($serialized, [
                'fieldsValues' => array_map(function (FieldChoiceCategory $fcc) {
                    return $this->fieldChoiceCategorySerializer->serialize($fcc);
                }, $this->fieldChoiceCategoryRepo->findBy(['category' => $category])),
            ]);
        }

        return $serialized;
    }

    /**
     * @param array $data
     *
     * @return Category
     */
    public function deserialize($data, Category $category, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $category);
        $this->sipe('details', 'setDetails', $data, $category);

        if (isset($data['clacoForm']['id']) && !in_array(Options::REFRESH_UUID, $options)) {
            $clacoForm = $this->clacoFormRepo->findOneBy(['uuid' => $data['clacoForm']['id']]);

            if (!empty($clacoForm)) {
                $category->setClacoForm($clacoForm);
            }
        }

        $category->emptyManagers();
        if (isset($data['managers'])) {
            foreach ($data['managers'] as $managerData) {
                $manager = $this->userRepo->findOneBy(['username' => $managerData['username']]);

                if (!empty($manager)) {
                    $category->addManager($manager);
                }
            }
        }

        if (isset($data['fieldsValues'])) {
            $this->deserializeFieldChoiceCategory($data['fieldsValues'], $category);
        }

        return $category;
    }

    /**
     * @param array $valuesData
     */
    private function deserializeFieldChoiceCategory($valuesData, Category $category)
    {
        $oldFieldChoiceCategories = $this->fieldChoiceCategoryRepo->findBy(['category' => $category]);
        $newUuids = [];

        foreach ($valuesData as $valueData) {
            if (isset($valueData['id'])) {
                $fieldChoiceCategory = $this->fieldChoiceCategoryRepo->findOneBy(['uuid' => $valueData['id']]);

                if (empty($fieldChoiceCategory)) {
                    $fieldChoiceCategory = new FieldChoiceCategory();
                    $fieldChoiceCategory->setCategory($category);
                }
                $this->fieldChoiceCategorySerializer->deserialize($valueData, $fieldChoiceCategory);
                $this->om->persist($fieldChoiceCategory);
                $newUuids[] = $fieldChoiceCategory->getUuid();
            }
        }

        foreach ($oldFieldChoiceCategories as $oldFieldChoiceCategory) {
            if (!in_array($oldFieldChoiceCategory->getUuid(), $newUuids)) {
                $this->om->remove($oldFieldChoiceCategory);
            }
        }
    }
}
