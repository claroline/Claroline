<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\ContentItem;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\ContentItemSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ContentItemValidator;

class ContentItemDefinition implements ItemDefinitionInterface
{
    public function __construct(
        private readonly ContentItemValidator $validator,
        private readonly ContentItemSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::CONTENT;
    }

    public static function getEntityClass(): string
    {
        return ContentItem::class;
    }

    protected function getItemValidator(): ContentItemValidator
    {
        return $this->validator;
    }

    protected function getItemSerializer(): ContentItemSerializer
    {
        return $this->serializer;
    }

    public function validateQuestion(array $question, array $options = []): array
    {
        return $this->getItemValidator()->validate($question, $options);
    }

    /**
     * Serializes a content item entity.
     *
     * @param ContentItem $question
     */
    public function serializeQuestion(AbstractItem $question, array $options = []): array
    {
        return $this->getItemSerializer()->serialize($question, $options);
    }

    /**
     * Deserializes content item data.
     *
     * @param ContentItem $question
     */
    public function deserializeQuestion(array $data, AbstractItem $question = null, array $options = []): ContentItem
    {
        return $this->getItemSerializer()->deserialize($data, $question, $options);
    }

    /**
     * No additional identifier to regenerate.
     *
     * @param ContentItem $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
    }
}
