<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;

/**
 * Interface for the definition of a quiz item type.
 */
interface ItemDefinitionInterface
{
    /**
     * Gets the mime type of the question.
     *
     * It MUST have the format : application/x.{QUESTION_TYPE}+json
     *
     * @return string
     */
    public static function getMimeType();

    /**
     * Gets the entity class holding the specific question data.
     *
     * This method needs to only return the class name, without namespace (eg. ChoiceQuestion).
     * The full namespace `UJM\ExoBundle\Entity\ItemType` is added as prefix to the return value.
     *
     * @return string
     */
    public static function getEntityClass();

    /**
     * Validates question data.
     *
     * @param \stdClass $question
     * @param array     $options
     *
     * @return array
     */
    public function validateQuestion(\stdClass $question, array $options = []);

    /**
     * Serializes question entity.
     *
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return \stdClass
     */
    public function serializeQuestion(AbstractItem $question, array $options = []);

    /**
     * Deserializes question data.
     *
     * @param \stdClass    $data
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return AbstractItem
     */
    public function deserializeQuestion(\stdClass $data, AbstractItem $question = null, array $options = []);

    /**
     * Applies an arbitrary parser on all HTML contents in the item definition.
     *
     * @param ContentParserInterface $contentParser
     * @param \stdClass              $item
     */
    public function parseContents(ContentParserInterface $contentParser, \stdClass $item);

    /**
     * Generates new UUIDs for the Item entities.
     *
     * This is used for duplication features as we don't know the internal structure of a type
     *
     * @param AbstractItem $item
     */
    public function refreshIdentifiers(AbstractItem $item);
}
