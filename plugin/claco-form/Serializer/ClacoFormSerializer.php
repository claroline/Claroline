<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\Keyword;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform")
 * @DI\Tag("claroline.serializer")
 */
class ClacoFormSerializer
{
    use SerializerTrait;

    /** @var CategorySerializer */
    private $categorySerializer;

    /** @var FieldSerializer */
    private $fieldSerializer;

    /** @var KeywordSerializer */
    private $keywordSerializer;

    /** @var ObjectManager */
    private $om;

    private $fieldRepo;

    /**
     * ClacoFormSerializer constructor.
     *
     * @DI\InjectParams({
     *     "categorySerializer" = @DI\Inject("claroline.serializer.clacoform.category"),
     *     "fieldSerializer"    = @DI\Inject("claroline.serializer.clacoform.field"),
     *     "keywordSerializer"  = @DI\Inject("claroline.serializer.clacoform.keyword"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param CategorySerializer $categorySerializer
     * @param FieldSerializer    $fieldSerializer
     * @param KeywordSerializer  $keywordSerializer
     * @param ObjectManager      $om
     */
    public function __construct(
        CategorySerializer $categorySerializer,
        FieldSerializer $fieldSerializer,
        KeywordSerializer $keywordSerializer,
        ObjectManager $om
    ) {
        $this->categorySerializer = $categorySerializer;
        $this->fieldSerializer = $fieldSerializer;
        $this->keywordSerializer = $keywordSerializer;
        $this->om = $om;

        $this->fieldRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Field');
    }

    /**
     * Serializes a ClacoForm entity for the JSON api.
     *
     * @param ClacoForm $clacoForm - the ClacoForm resource to serialize
     * @param array     $options   - a list of serialization options
     *
     * @return array - the serialized representation of the ClacoForm resource
     */
    public function serialize(ClacoForm $clacoForm, array $options = [])
    {
        $serialized = [
            'id' => $clacoForm->getUuid(),
            'autoId' => $clacoForm->getId(),
            'template' => $clacoForm->getTemplate(),
            'details' => [
                'max_entries' => $clacoForm->getMaxEntries(),
                'creation_enabled' => $clacoForm->isCreationEnabled(),
                'edition_enabled' => $clacoForm->isEditionEnabled(),
                'moderated' => $clacoForm->isModerated(),
                'default_home' => $clacoForm->getDefaultHome(),
                'display_nb_entries' => $clacoForm->getDisplayNbEntries(),
                'menu_position' => $clacoForm->getMenuPosition(),
                'random_enabled' => $clacoForm->isRandomEnabled(),
                'random_categories' => $clacoForm->getRandomCategories(),
                'random_start_date' => $clacoForm->getRandomStartDate(),
                'random_end_date' => $clacoForm->getRandomEndDate(),
                'search_enabled' => $clacoForm->getSearchEnabled(),
                'search_column_enabled' => $clacoForm->isSearchColumnEnabled(),
                'search_columns' => $clacoForm->getSearchColumns(),
                'display_metadata' => $clacoForm->getDisplayMetadata(),
                'locked_fields_for' => $clacoForm->getLockedFieldsFor(),
                'display_categories' => $clacoForm->getDisplayCategories(),
                'open_categories' => $clacoForm->getOpenCategories(),
                'comments_enabled' => $clacoForm->isCommentsEnabled(),
                'anonymous_comments_enabled' => $clacoForm->isAnonymousCommentsEnabled(),
                'moderate_comments' => $clacoForm->getModerateComments(),
                'display_comments' => $clacoForm->getDisplayComments(),
                'open_comments' => $clacoForm->getOpenComments(),
                'display_comment_author' => $clacoForm->getDisplayCommentAuthor(),
                'display_comment_date' => $clacoForm->getDisplayCommentDate(),
                'comments_roles' => $clacoForm->getCommentsRoles(),
                'comments_display_roles' => $clacoForm->getCommentsDisplayRoles(),
                'votes_enabled' => $clacoForm->isVotesEnabled(),
                'display_votes' => $clacoForm->getDisplayVotes(),
                'open_votes' => $clacoForm->getOpenVotes(),
                'votes_start_date' => $clacoForm->getVotesStartDate(),
                'votes_end_date' => $clacoForm->getVotesEndDate(),
                'keywords_enabled' => $clacoForm->isKeywordsEnabled(),
                'new_keywords_enabled' => $clacoForm->isNewKeywordsEnabled(),
                'display_keywords' => $clacoForm->getDisplayKeywords(),
                'open_keywords' => $clacoForm->getOpenKeywords(),
                'use_template' => $clacoForm->getUseTemplate(),
                'default_display_mode' => $clacoForm->getDefaultDisplayMode(),
                'display_title' => $clacoForm->getDisplayTitle(),
                'display_subtitle' => $clacoForm->getDisplaySubtitle(),
                'display_content' => $clacoForm->getDisplayContent(),
                'title_field_label' => $clacoForm->getTitleFieldLabel(),
                'search_restricted' => $clacoForm->isSearchRestricted(),
                'search_restricted_columns' => $clacoForm->getSearchRestrictedColumns(),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'categories' => array_map(function (Category $category) {
                    return $this->categorySerializer->serialize($category);
                }, $clacoForm->getCategories()),
            ]);
            $serialized = array_merge($serialized, [
                'keywords' => array_map(function (Keyword $keyword) {
                    return $this->keywordSerializer->serialize($keyword);
                }, $clacoForm->getKeywords()),
            ]);
            $serialized = array_merge($serialized, [
                'fields' => array_map(function (Field $field) {
                    return $this->fieldSerializer->serialize($field);
                }, $clacoForm->getFields()),
            ]);
        }

        return $serialized;
    }

    /**
     * @param array     $data
     * @param ClacoForm $clacoForm
     *
     * @return ClacoForm
     */
    public function deserialize($data, ClacoForm $clacoForm)
    {
        $this->sipe('details', 'setDetails', $data, $clacoForm);

        $oldFields = $clacoForm->getFields();
        $newFieldsUuids = [];
        $clacoForm->emptyFields();

        foreach ($data['fields'] as $fieldData) {
            if (isset($fieldData['id'])) {
                $newFieldsUuids[] = $fieldData['id'];
            }
            $field = isset($fieldData['id']) ? $this->fieldRepo->findOneBy(['uuid' => $fieldData['id']]) : null;

            if (empty($field)) {
                $field = new Field();
                $field->setClacoForm($clacoForm);
            }
            $newField = $this->fieldSerializer->deserialize($fieldData, $field);
            $this->om->persist($newField);

            $clacoForm->addField($newField);
        }
        $this->om->startFlushSuite();

        /* Removes previous fields that are not used anymore */
        foreach ($oldFields as $field) {
            if (!in_array($field->getUuid(), $newFieldsUuids)) {
                $this->deleteField($field);
            }
        }
        $this->om->endFlushSuite();

        return $clacoForm;
    }

    /**
     * @param Field $field
     */
    private function deleteField(Field $field)
    {
        $fieldFacet = $field->getFieldFacet();

        if (!is_null($fieldFacet)) {
            $choices = $fieldFacet->getFieldFacetChoices();

            foreach ($choices as $choice) {
                $this->om->remove($choice);
            }
            $this->om->remove($fieldFacet);
        }
        $this->om->remove($field);
    }
}
