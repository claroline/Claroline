<?php

namespace Claroline\ClacoFormBundle\API\Serializer;

use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\CoreBundle\API\Serializer\UserSerializer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.entry")
 * @DI\Tag("claroline.serializer")
 */
class EntrySerializer
{
    const OPTION_MINIMAL = 'minimal';

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

    /**
     * EntrySerializer constructor.
     *
     * @DI\InjectParams({
     *     "categorySerializer"   = @DI\Inject("claroline.serializer.clacoform.category"),
     *     "commentSerializer"    = @DI\Inject("claroline.serializer.clacoform.comment"),
     *     "fieldValueSerializer" = @DI\Inject("claroline.serializer.clacoform.field_value"),
     *     "keywordSerializer"    = @DI\Inject("claroline.serializer.clacoform.keyword"),
     *     "userSerializer"       = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param CategorySerializer   $categorySerializer
     * @param CommentSerializer    $commentSerializer
     * @param FieldValueSerializer $fieldValueSerializer
     * @param KeywordSerializer    $keywordSerializer
     * @param UserSerializer       $userSerializer
     */
    public function __construct(
        CategorySerializer $categorySerializer,
        CommentSerializer $commentSerializer,
        FieldValueSerializer $fieldValueSerializer,
        KeywordSerializer $keywordSerializer,
        UserSerializer $userSerializer
    ) {
        $this->categorySerializer = $categorySerializer;
        $this->commentSerializer = $commentSerializer;
        $this->fieldValueSerializer = $fieldValueSerializer;
        $this->keywordSerializer = $keywordSerializer;
        $this->userSerializer = $userSerializer;
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
            'id' => $entry->getId(),
            'title' => $entry->getTitle(),
            'status' => $entry->getStatus(),
            'creationDate' => $entry->getCreationDate() ? $entry->getCreationDate()->format('Y-m-d H:i:s') : null,
            'editionDate' => $entry->getEditionDate() ? $entry->getEditionDate()->format('Y-m-d H:i:s') : null,
            'publicationDate' => $entry->getPublicationDate() ? $entry->getPublicationDate()->format('Y-m-d H:i:s') : null,
            'user' => $user ? $this->userSerializer->serialize($user) : null,
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'categories' => $this->getCategories($entry),
                'keywords' => $this->getKeywords($entry),
                'comments' => $this->getComments($entry),
                'fieldValues' => $this->getFieldValues($entry),
            ]);
        }

        return $serialized;
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

    private function getFieldValues(Entry $entry)
    {
        return array_map(
            function (FieldValue $fieldValue) {
                return $this->fieldValueSerializer->serialize($fieldValue);
            },
            $entry->getFieldValues()
        );
    }
}
