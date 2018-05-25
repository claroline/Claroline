<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.entry")
 * @DI\Tag("claroline.serializer")
 */
class EntrySerializer
{
    use SerializerTrait;

    /** @var ClacoFormManager */
    private $clacoFormManager;

    /** @var CategorySerializer */
    private $categorySerializer;

    /** @var CommentSerializer */
    private $commentSerializer;

    /** @var FieldValueSerializer */
    private $fieldValueSerializer;

    /** @var KeywordSerializer */
    private $keywordSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var ObjectManager */
    private $om;

    private $clacoFormRepo;
    private $fieldRepo;
    private $fieldValueRepo;
    private $fieldChoiceCategoryRepo;
    private $categoryRepo;
    private $keywordRepo;
    private $userRepo;

    /**
     * EntrySerializer constructor.
     *
     * @DI\InjectParams({
     *     "clacoFormManager"     = @DI\Inject("claroline.manager.claco_form_manager"),
     *     "categorySerializer"   = @DI\Inject("claroline.serializer.clacoform.category"),
     *     "commentSerializer"    = @DI\Inject("claroline.serializer.clacoform.comment"),
     *     "fieldValueSerializer" = @DI\Inject("claroline.serializer.clacoform.field_value"),
     *     "keywordSerializer"    = @DI\Inject("claroline.serializer.clacoform.keyword"),
     *     "userSerializer"       = @DI\Inject("claroline.serializer.user"),
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ClacoFormManager     $clacoFormManager
     * @param CategorySerializer   $categorySerializer
     * @param CommentSerializer    $commentSerializer
     * @param FieldValueSerializer $fieldValueSerializer
     * @param KeywordSerializer    $keywordSerializer
     * @param UserSerializer       $userSerializer
     * @param ObjectManager        $om
     */
    public function __construct(
        ClacoFormManager $clacoFormManager,
        CategorySerializer $categorySerializer,
        CommentSerializer $commentSerializer,
        FieldValueSerializer $fieldValueSerializer,
        KeywordSerializer $keywordSerializer,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->clacoFormManager = $clacoFormManager;
        $this->categorySerializer = $categorySerializer;
        $this->commentSerializer = $commentSerializer;
        $this->fieldValueSerializer = $fieldValueSerializer;
        $this->keywordSerializer = $keywordSerializer;
        $this->userSerializer = $userSerializer;
        $this->om = $om;

        $this->clacoFormRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
        $this->fieldRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Field');
        $this->fieldValueRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\FieldValue');
        $this->fieldChoiceCategoryRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\FieldChoiceCategory');
        $this->categoryRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Category');
        $this->keywordRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Keyword');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * Serializes an Entry entity for the JSON api.
     *
     * @param Entry $entry   - the entry to serialize
     * @param array $options - a list of serialization options
     *
     * @return array - the serialized representation of the entry
     */
    public function serialize(Entry $entry, array $options = [])
    {
        $user = $entry->getUser();

        $serialized = [
            'id' => $entry->getUuid(),
            'autoId' => $entry->getId(),
            'title' => $entry->getTitle(),
            'status' => $entry->getStatus(),
            'locked' => $entry->isLocked(),
            'creationDate' => $entry->getCreationDate() ? $entry->getCreationDate()->format('Y-m-d H:i:s') : null,
            'editionDate' => $entry->getEditionDate() ? $entry->getEditionDate()->format('Y-m-d H:i:s') : null,
            'publicationDate' => $entry->getPublicationDate() ? $entry->getPublicationDate()->format('Y-m-d H:i:s') : null,
            'user' => $user ? $this->userSerializer->serialize($user, [Options::SERIALIZE_MINIMAL]) : null,
            'clacoForm' => [
                'id' => $entry->getClacoForm()->getUuid(),
            ],
        ];
        $fieldValues = $entry->getFieldValues();

        if (count($fieldValues) > 0) {
            $serialized['values'] = $this->serializeValues($fieldValues);
        }
        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'categories' => $this->getCategories($entry),
                'keywords' => $this->getKeywords($entry),
                'comments' => $this->getComments($entry),
            ]);
        }

        return $serialized;
    }

    /**
     * @param array $data
     * @param Entry $entry
     *
     * @return Entry
     */
    public function deserialize($data, Entry $entry)
    {
        $this->sipe('title', 'setTitle', $data, $entry);
        $this->sipe('status', 'setStatus', $data, $entry);

        if (isset($data['user']['id'])) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['user']['id']]);

            if (!empty($user)) {
                $entry->setUser($user);
            }
        }
        /* TODO: checks rights */
        $this->deserializeCategories($entry, $data['categories']);
        $this->deserializeKeywords($entry, $data['keywords']);

        if (isset($data['clacoForm']['id'])) {
            $clacoForm = $this->clacoFormRepo->findOneBy(['uuid' => $data['clacoForm']['id']]);

            if (!empty($clacoForm)) {
                $entry->setClacoForm($clacoForm);

                // Initializes status
                if (empty($entry->getStatus())) {
                    $status = $clacoForm->isModerated() ? Entry::PENDING : Entry::PUBLISHED;
                    $entry->setStatus($status);
                }

                // Sets values for fields
                $fields = $this->fieldRepo->findBy(['clacoForm' => $clacoForm]);

                foreach ($fields as $field) {
                    $uuid = $field->getUuid();

                    if (isset($data['values'][$uuid])) {
                        $fieldValue = $this->fieldValueRepo->findOneBy(['entry' => $entry, 'field' => $field]);

                        if (empty($fieldValue)) {
                            $fieldValue = new FieldValue();
                            $fieldValue->setEntry($entry);
                            $fieldValue->setField($field);

                            $fielFacetValue = new FieldFacetValue();
                            $fielFacetValue->setUser($entry->getUser());
                            $fielFacetValue->setFieldFacet($field->getFieldFacet());
                            $fielFacetValue->setValue($data['values'][$uuid]);
                            $fieldValue->setFieldFacetValue($fielFacetValue);

                            $this->om->persist($fielFacetValue);
                        } else {
                            $fieldValue->setValue($data['values'][$uuid]);
                        }
                        $this->om->persist($fieldValue);

                        $fieldsCategories = $this->fieldChoiceCategoryRepo->findBy(['field' => $field]);

                        foreach ($fieldsCategories as $fieldCategory) {
                            switch ($field->getType()) {
                                case FieldFacet::NUMBER_TYPE:
                                    $isCategoryValue = floatval($data['values'][$uuid]) === floatval($fieldCategory->getValue());
                                    break;
                                default:
                                    $isCategoryValue = $data['values'][$uuid] === $fieldCategory->getValue();
                            }
                            if ($isCategoryValue) {
                                $entry->addCategory($fieldCategory->getCategory());
                            }
                        }
                    }
                }
            }
        }
        $currentDate = new \DateTime();

        if (empty($entry->getCreationDate())) {
            $entry->setCreationDate($currentDate);
            $this->clacoFormManager->notifyCategoriesManagers($entry, [], $entry->getCategories());
        } else {
            $entry->setEditionDate($currentDate);
            $this->clacoFormManager->notifyCategoriesManagers($entry, $entry->getCategories(), $entry->getCategories());
        }

        return $entry;
    }

    private function serializeValues(array $fieldValues)
    {
        $values = [];

        foreach ($fieldValues as $fieldValue) {
            $field = $fieldValue->getField();
            $values[$field->getUuid()] = $fieldValue->getValue();
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
                    $this->om->persist($keyword);
                    $entry->addKeyword($keyword);
                }
            }
        }

        return $entry;
    }
}
