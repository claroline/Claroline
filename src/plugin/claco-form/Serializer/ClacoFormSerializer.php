<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;

class ClacoFormSerializer
{
    use SerializerTrait;

    public function __construct(
        private CategorySerializer $categorySerializer,
        private FieldSerializer $fieldSerializer,
        private KeywordSerializer $keywordSerializer,
        private ObjectManager $om
    ) {
    }

    public function getName(): string
    {
        return 'clacoform';
    }

    public function getClass(): string
    {
        return ClacoForm::class;
    }

    /**
     * Serializes a ClacoForm entity for the JSON api.
     *
     * @param ClacoForm $clacoForm - the ClacoForm resource to serialize
     * @param array     $options   - a list of serialization options
     *
     * @return array - the serialized representation of the ClacoForm resource
     */
    public function serialize(ClacoForm $clacoForm, array $options = []): array
    {
        $serialized = [
            'id' => $clacoForm->getUuid(),

            // TODO : break into multiple sub object
            // TODO : use camelCase
            'details' => [
                'max_entries' => $clacoForm->getMaxEntries(),
                'creation_enabled' => $clacoForm->isCreationEnabled(),
                'edition_enabled' => $clacoForm->isEditionEnabled(),
                'moderated' => $clacoForm->isModerated(),
                'default_home' => $clacoForm->getDefaultHome(),
                'menu_position' => $clacoForm->getMenuPosition(),
                'search_enabled' => $clacoForm->getSearchEnabled(),
                'display_metadata' => $clacoForm->getDisplayMetadata(),
                'display_categories' => $clacoForm->getDisplayCategories(),
                'comments_enabled' => $clacoForm->isCommentsEnabled(),
                'anonymous_comments_enabled' => $clacoForm->isAnonymousCommentsEnabled(),
                'moderate_comments' => $clacoForm->getModerateComments(),
                'display_comments' => $clacoForm->getDisplayComments(),
                'open_comments' => $clacoForm->getOpenComments(),
                'display_comment_author' => $clacoForm->getDisplayCommentAuthor(),
                'display_comment_date' => $clacoForm->getDisplayCommentDate(),
                'comments_roles' => $clacoForm->getCommentsRoles(),
                'comments_display_roles' => $clacoForm->getCommentsDisplayRoles(),
                'keywords_enabled' => $clacoForm->isKeywordsEnabled(),
                'new_keywords_enabled' => $clacoForm->isNewKeywordsEnabled(),
                'display_keywords' => $clacoForm->getDisplayKeywords(),
                'display_title' => $clacoForm->getDisplayTitle(),
                'display_subtitle' => $clacoForm->getDisplaySubtitle(),
                'display_content' => $clacoForm->getDisplayContent(),
                'title_field_label' => $clacoForm->getTitleFieldLabel(),
                'helpMessage' => $clacoForm->getHelpMessage(),
            ],

            'display' => [
                'statistics' => $clacoForm->hasStatistics(),
                'showEntryNav' => $clacoForm->getShowEntryNav(),
                'showConfirm' => $clacoForm->getShowConfirm(),
                'confirmMessage' => $clacoForm->getConfirmMessage(),
            ],

            'random' => [
                'enabled' => $clacoForm->isRandomEnabled(),
                'categories' => $clacoForm->getRandomCategories(),
                'dates' => DateRangeNormalizer::normalize(
                    $clacoForm->getRandomStartDate(),
                    $clacoForm->getRandomEndDate()
                ),
            ],

            'template' => [
                'enabled' => $clacoForm->getUseTemplate(),
                'content' => $clacoForm->getTemplate(),
            ],

            // entry list config
            // todo : big c/c from Claroline\CoreBundle\API\Serializer\Widget\Type\ListWidgetSerializer
            'list' => [
                'actions' => $clacoForm->hasActions(),
                'count' => $clacoForm->hasCount(),
                // display feature
                'display' => $clacoForm->getDisplay(),
                'availableDisplays' => $clacoForm->getAvailableDisplays(),

                // sort feature
                'sorting' => $clacoForm->getSortBy(),
                'availableSort' => $clacoForm->getAvailableSort(),

                // filter feature
                'searchMode' => $clacoForm->getSearchMode(),
                'filters' => $clacoForm->getFilters(),
                'availableFilters' => $clacoForm->getAvailableFilters(),

                // pagination feature
                'paginated' => $clacoForm->isPaginated(),
                'pageSize' => $clacoForm->getPageSize(),
                'availablePageSizes' => $clacoForm->getAvailablePageSizes(),

                // table config
                'columns' => $clacoForm->getDisplayedColumns(),
                'availableColumns' => $clacoForm->getAvailableColumns(),

                // grid config
                'card' => [
                    'display' => $clacoForm->getCard(),
                    'mapping' => [], // TODO : grab custom ClacoForm config when standard list can handle it
                ],
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'fields' => array_map(function (Field $field) {
                    return $this->fieldSerializer->serialize($field);
                }, $clacoForm->getFields()),
            ]);

            // TODO : should not be managed here (they have their own API for the UI). This is no longer used.
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
        }

        return $serialized;
    }

    public function deserialize(array $data, ClacoForm $clacoForm, array $options = []): ClacoForm
    {
        // TODO : remove and call all setters individually
        $this->sipe('details', 'setDetails', $data, $clacoForm);
        $this->sipe('details.helpMessage', 'setHelpMessage', $data, $clacoForm);

        // display
        $this->sipe('display.showEntryNav', 'setShowEntryNav', $data, $clacoForm);
        $this->sipe('display.showConfirm', 'setShowConfirm', $data, $clacoForm);
        $this->sipe('display.confirmMessage', 'setConfirmMessage', $data, $clacoForm);
        $this->sipe('display.statistics', 'setStatistics', $data, $clacoForm);

        // random feature
        $this->sipe('random.enabled', 'setRandomEnabled', $data, $clacoForm);
        $this->sipe('random.categories', 'setRandomCategories', $data, $clacoForm);
        if (isset($data['random']['dates'])) {
            $dateRange = DateRangeNormalizer::denormalize($data['random']['dates']);

            $clacoForm->setRandomStartDate($dateRange[0]);
            $clacoForm->setRandomEndDate($dateRange[1]);
        }

        // entries template
        if (isset($data['template'])) {
            $this->sipe('template.enabled', 'setUseTemplate', $data, $clacoForm);
            $this->sipe('template.content', 'setTemplate', $data, $clacoForm);
        }

        // fields
        $oldFields = $clacoForm->getFields();
        $newFieldsUuids = [];
        $clacoForm->emptyFields();

        if (!in_array(Options::REFRESH_UUID, $options) && isset($data['fields'])) {
            foreach ($data['fields'] as $fieldData) {
                if (isset($fieldData['id'])) {
                    $newFieldsUuids[] = $fieldData['id'];
                }
                $field = isset($fieldData['id']) ? $this->om->getRepository(Field::class)->findByFieldFacetUuid($fieldData['id']) : null;

                if (empty($field)) {
                    $field = new Field();
                    $field->setClacoForm($clacoForm);
                }
                $newField = $this->fieldSerializer->deserialize($fieldData, $field);
                $this->om->persist($newField);

                $clacoForm->addField($newField);
            }
            $this->om->startFlushSuite();

            // Removes previous fields that are not used anymore
            foreach ($oldFields as $field) {
                if (!in_array($field->getUuid(), $newFieldsUuids)) {
                    $this->deleteField($field);
                }
            }
            $this->om->endFlushSuite();
        }

        // entry list config
        // todo : big c/c from Claroline\CoreBundle\API\Serializer\Widget\Type\ListWidgetSerializer
        $this->sipe('list.count', 'setCount', $data, $clacoForm);
        $this->sipe('list.actions', 'setActions', $data, $clacoForm);

        // display feature
        $this->sipe('list.display', 'setDisplay', $data, $clacoForm);
        $this->sipe('list.availableDisplays', 'setAvailableDisplays', $data, $clacoForm);

        // sort feature
        $this->sipe('list.sorting', 'setSortBy', $data, $clacoForm);
        $this->sipe('list.availableSort', 'setAvailableSort', $data, $clacoForm);

        // filter feature
        $this->sipe('list.searchMode', 'setSearchMode', $data, $clacoForm);
        $this->sipe('list.filters', 'setFilters', $data, $clacoForm);
        $this->sipe('list.availableFilters', 'setAvailableFilters', $data, $clacoForm);

        // pagination feature
        $this->sipe('list.paginated', 'setPaginated', $data, $clacoForm);
        $this->sipe('list.pageSize', 'setPageSize', $data, $clacoForm);
        $this->sipe('list.availablePageSizes', 'setAvailablePageSizes', $data, $clacoForm);

        // table config
        $this->sipe('list.columns', 'setDisplayedColumns', $data, $clacoForm);
        $this->sipe('list.availableColumns', 'setAvailableColumns', $data, $clacoForm);

        // grid config
        $this->sipe('list.card.display', 'setCard', $data, $clacoForm);

        return $clacoForm;
    }

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
