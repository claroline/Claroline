<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\ItemType\AbstractItem;

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
     * @return array
     */
    public function validateQuestion(array $question, array $options = []);

    /**
     * Serializes question entity.
     *
     * @return array
     */
    public function serializeQuestion(AbstractItem $question, array $options = []);

    /**
     * Deserializes question data.
     *
     * @param AbstractItem $question
     *
     * @return AbstractItem
     */
    public function deserializeQuestion(array $data, AbstractItem $question = null, array $options = []);

    /**
     * Generates new UUIDs for the Item entities.
     *
     * This is used for duplication features as we don't know the internal structure of a type
     */
    public function refreshIdentifiers(AbstractItem $item);
}
