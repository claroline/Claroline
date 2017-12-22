<?php

namespace Claroline\ClacoFormBundle\API\Serializer;

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
    const OPTION_MINIMAL = 'minimal';

    /** @var CategorySerializer */
    private $categorySerializer;

    /** @var FieldSerializer */
    private $fieldSerializer;

    /** @var KeywordSerializer */
    private $keywordSerializer;

    /**
     * ClacoFormSerializer constructor.
     *
     * @DI\InjectParams({
     *     "categorySerializer" = @DI\Inject("claroline.serializer.clacoform.category"),
     *     "fieldSerializer"    = @DI\Inject("claroline.serializer.clacoform.field"),
     *     "keywordSerializer"  = @DI\Inject("claroline.serializer.clacoform.keyword")
     * })
     *
     * @param CategorySerializer $categorySerializer
     * @param FieldSerializer    $fieldSerializer
     * @param KeywordSerializer  $keywordSerializer
     */
    public function __construct(
        CategorySerializer $categorySerializer,
        FieldSerializer $fieldSerializer,
        KeywordSerializer $keywordSerializer
    ) {
        $this->categorySerializer = $categorySerializer;
        $this->fieldSerializer = $fieldSerializer;
        $this->keywordSerializer = $keywordSerializer;
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
            'id' => $clacoForm->getId(),
            'template' => $clacoForm->getTemplate(),
            'details' => $clacoForm->getDetails(),
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
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
}
