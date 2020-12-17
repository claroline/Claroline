<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\ContentItemSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ContentItemValidator;

/**
 * Content item definition.
 */
class ContentItemDefinition implements ItemDefinitionInterface
{
    /**
     * @var ContentItemValidator
     */
    private $validator;

    /**
     * @var ContentItemSerializer
     */
    private $serializer;

    /**
     * ContentItemDefinition constructor.
     *
     * @param ContentItemValidator  $validator
     * @param ContentItemSerializer $serializer
     */
    public function __construct(ContentItemValidator $validator, ContentItemSerializer $serializer)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the content item mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::CONTENT;
    }

    /**
     * Gets the text content item entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\ContentItem';
    }

    /**
     * Gets the content item validator.
     *
     * @return ContentItemValidator
     */
    protected function getItemValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the content item serializer.
     *
     * @return ContentItemSerializer
     */
    protected function getItemSerializer()
    {
        return $this->serializer;
    }

    /**
     * Validates a content item.
     *
     * @param array $item
     * @param array $options
     *
     * @return array
     */
    public function validateQuestion(array $item, array $options = [])
    {
        return $this->getItemValidator()->validate($item, $options);
    }

    /**
     * Serializes a content item entity.
     *
     * @param AbstractItem $item
     * @param array        $options
     *
     * @return array
     */
    public function serializeQuestion(AbstractItem $item, array $options = [])
    {
        return $this->getItemSerializer()->serialize($item, $options);
    }

    /**
     * Deserializes content item data.
     *
     * @param array        $itemData
     * @param AbstractItem $item
     * @param array        $options
     *
     * @return AbstractItem
     */
    public function deserializeQuestion(array $itemData, AbstractItem $item = null, array $options = [])
    {
        return $this->getItemSerializer()->deserialize($itemData, $item, $options);
    }

    /**
     * No additional identifier to regenerate.
     *
     * @param AbstractItem $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        return;
    }
}
