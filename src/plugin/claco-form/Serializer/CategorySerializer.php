<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Persistence\ObjectRepository;

class CategorySerializer
{
    use SerializerTrait;

    private UserRepository $userRepo;
    private ObjectRepository $fieldChoiceCategoryRepo;

    public function __construct(
        private readonly FieldChoiceCategorySerializer $fieldChoiceCategorySerializer,
        private readonly UserSerializer $userSerializer,
        private readonly ObjectManager $om
    ) {
        $this->fieldChoiceCategoryRepo = $om->getRepository(FieldChoiceCategory::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName(): string
    {
        return 'clacoform_category';
    }

    public function getClass(): string
    {
        return Category::class;
    }

    /**
     * Serializes a Category entity for the JSON api.
     *
     * @param Category $category - the category to serialize
     * @param array    $options  - a list of serialization options
     *
     * @return array - the serialized representation of the category
     */
    public function serialize(Category $category, array $options = []): array
    {
        $serialized = [
            'id' => $category->getUuid(),
            'name' => $category->getName(),
            'details' => $category->getDetails(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'managers' => array_map(function (User $manager) {
                    return $this->userSerializer->serialize($manager, [SerializerInterface::SERIALIZE_MINIMAL]);
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

    public function deserialize(array $data, Category $category, array $options = []): Category
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $category);
        } else {
            $category->refreshUuid();
        }

        $this->sipe('name', 'setName', $data, $category);
        $this->sipe('details', 'setDetails', $data, $category);

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

    private function deserializeFieldChoiceCategory(array $valuesData, Category $category): void
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
