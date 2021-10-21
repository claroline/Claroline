<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\FacetManager;

class EntrySerializer
{
    use SerializerTrait;

    /** @var CategorySerializer */
    private $categorySerializer;
    /** @var CommentSerializer */
    private $commentSerializer;
    /** @var KeywordSerializer */
    private $keywordSerializer;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var FacetManager */
    private $facetManager;

    private $clacoFormRepo;
    private $fieldRepo;
    private $categoryRepo;
    private $keywordRepo;
    private $userRepo;

    public function __construct(
        ObjectManager $om,
        CategorySerializer $categorySerializer,
        CommentSerializer $commentSerializer,
        KeywordSerializer $keywordSerializer,
        UserSerializer $userSerializer,
        FacetManager $facetManager
    ) {
        $this->categorySerializer = $categorySerializer;
        $this->commentSerializer = $commentSerializer;
        $this->keywordSerializer = $keywordSerializer;
        $this->userSerializer = $userSerializer;
        $this->facetManager = $facetManager;

        $this->clacoFormRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
        $this->fieldRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Field');
        $this->categoryRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Category');
        $this->keywordRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Keyword');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    public function getName()
    {
        return 'clacoform_entry';
    }

    /**
     * Serializes an Entry entity for the JSON api.
     *
     * @param Entry $entry   - the entry to serialize
     * @param array $options - a list of serialization options
     *
     * @return array - the serialized representation of the entry
     */
    public function serialize(Entry $entry, array $options = []): array
    {
        $user = $entry->getUser();

        $serialized = [
            'id' => $entry->getUuid(),
            'autoId' => $entry->getId(),
            'title' => $entry->getTitle(),
            'status' => $entry->getStatus(),
            'locked' => $entry->isLocked(),
            'creationDate' => DateNormalizer::normalize($entry->getCreationDate()),
            'editionDate' => DateNormalizer::normalize($entry->getEditionDate()),
            'publicationDate' => DateNormalizer::normalize($entry->getPublicationDate()),
            'user' => $user ? $this->userSerializer->serialize($user, [Options::SERIALIZE_MINIMAL]) : null,
            'clacoForm' => [
                'id' => $entry->getClacoForm()->getUuid(),
            ],
            'values' => $this->serializeValues($entry),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'categories' => $this->getCategories($entry),
                'keywords' => $this->getKeywords($entry),
                'comments' => $this->getComments($entry),
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Entry $entry, array $options = []): Entry
    {
        $currentDate = new \DateTime();

        $this->sipe('title', 'setTitle', $data, $entry);
        $this->sipe('status', 'setStatus', $data, $entry);

        if (isset($data['user']['id'])) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['user']['id']]);

            if (!empty($user)) {
                $entry->setUser($user);
            }
        }
        if (isset($data['clacoForm']['id']) && !$entry->getClacoForm()) {
            $clacoForm = $this->clacoFormRepo->findOneBy(['uuid' => $data['clacoForm']['id']]);
            $entry->setClacoForm($clacoForm);
        }

        /* TODO: checks rights */
        if (isset($data['categories'])) {
            $this->deserializeCategories($entry, $data['categories']);
        }
        if (isset($data['keywords'])) {
            $this->deserializeKeywords($entry, $data['keywords']);
        }

        if ($entry->getClacoForm()) {
            $clacoForm = $entry->getClacoForm();

            // Initializes status
            if (empty($entry->getStatus())) {
                $status = $clacoForm->isModerated() ? Entry::PENDING : Entry::PUBLISHED;
                $entry->setStatus($status);

                if (Entry::PUBLISHED === $status) {
                    $entry->setPublicationDate($currentDate);
                }
            }

            // Sets values for fields

            /** @var Field[] $fields */
            $fields = $this->fieldRepo->findBy(['clacoForm' => $clacoForm]);
            foreach ($fields as $field) {
                $uuid = $field->getUuid();

                if (array_key_exists($uuid, $data['values'])) {
                    $fieldValue = $entry->getFieldValue($field);
                    if (empty($fieldValue)) {
                        $fieldValue = new FieldValue();
                        $fieldValue->setEntry($entry);
                        $fieldValue->setField($field);

                        $fieldFacetValue = new FieldFacetValue();
                        $fieldFacetValue->setUser($entry->getUser());
                        $fieldFacetValue->setFieldFacet($field->getFieldFacet());
                        $fieldValue->setFieldFacetValue($fieldFacetValue);

                        $entry->addFieldValue($fieldValue);
                    }

                    $fieldValue->setValue(
                        $this->facetManager->deserializeFieldValue(
                            $entry,
                            $field->getType(),
                            $data['values'][$uuid]
                        )
                    );
                }
            }
        }

        return $entry;
    }

    private function serializeValues(Entry $entry)
    {
        $fieldValues = $entry->getFieldValues();

        $values = [];
        foreach ($fieldValues as $fieldValue) {
            $field = $fieldValue->getField();
            $values[$field->getUuid()] = $this->facetManager->serializeFieldValue(
                $entry,
                $field->getType(),
                $fieldValue->getValue()
            );
        }

        return $values;
    }

    private function getCategories(Entry $entry)
    {
        return array_map(
            function (Category $category) {
                return $this->categorySerializer->serialize($category);
            },
            $entry->getCategories()
        );
    }

    private function getKeywords(Entry $entry)
    {
        return $entry->getClacoForm()->isKeywordsEnabled() ?
            array_map(
                function (Keyword $keyword) {
                    return $this->keywordSerializer->serialize($keyword);
                },
                $entry->getKeywords()
            ) :
            [];
    }

    private function getComments(Entry $entry)
    {
        return $entry->getClacoForm()->isCommentsEnabled() ?
            array_map(
                function (Comment $comment) {
                    return $this->commentSerializer->serialize($comment);
                },
                $entry->getComments()
            ) :
            [];
    }

    private function deserializeCategories(Entry $entry, array $categoriesData)
    {
        $entry->emptyCategories();

        foreach ($categoriesData as $categoryData) {
            $category = $this->categoryRepo->findOneBy(['uuid' => $categoryData['id']]);

            if (!empty($category)) {
                $entry->addCategory($category);
            }
        }

        return $entry;
    }

    private function deserializeKeywords(Entry $entry, array $keywordsData)
    {
        $entry->emptyKeywords();

        foreach ($keywordsData as $keywordData) {
            $keyword = $this->keywordRepo->findOneBy(['uuid' => $keywordData['id']]);

            if (!empty($keyword)) {
                $entry->addKeyword($keyword);
            } else {
                $clacoForm = $entry->getClacoForm();

                if ($clacoForm->isNewKeywordsEnabled()) {
                    $keyword = new Keyword();
                    $keyword->setClacoForm($clacoForm);
                    $this->keywordSerializer->deserialize($keywordData, $keyword);

                    $entry->addKeyword($keyword);
                }
            }
        }

        return $entry;
    }
}
