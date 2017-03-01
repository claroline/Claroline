<?php

namespace UJM\ExoBundle\Library\Item;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Item\Definition\ContentItemDefinition;
use UJM\ExoBundle\Library\Item\Definition\Exception\UnregisterableDefinitionException;
use UJM\ExoBundle\Library\Item\Definition\Exception\UnregisteredDefinitionException;
use UJM\ExoBundle\Library\Item\Definition\ItemDefinitionInterface;

/**
 * Collects definition class for each Item type defined.
 *
 * @DI\Service("ujm_exo.collection.item_definitions")
 */
class ItemDefinitionsCollection
{
    /**
     * The list of registered item definitions.
     *
     * @var ItemDefinitionInterface[]
     */
    private $definitions = [];

    /**
     * Adds a item definition to the collection.
     *
     * @param ItemDefinitionInterface $definition
     *
     * @throws UnregisterableDefinitionException
     */
    public function addDefinition(ItemDefinitionInterface $definition)
    {
        if (!is_string($definition->getMimeType())) {
            throw UnregisterableDefinitionException::notAStringMimeType($definition);
        }

        if (!in_array($definition->getMimeType(), ItemType::getList())) {
            throw UnregisterableDefinitionException::unsupportedMimeType($definition);
        }

        if ($this->has($definition->getMimeType())) {
            throw UnregisterableDefinitionException::duplicateMimeType($definition);
        }

        $this->definitions[$definition->getMimeType()] = $definition;
    }

    /**
     * Adds a content item definition to the collection.
     *
     * @param ContentItemDefinitionInterface $definition
     *
     * @throws UnregisterableDefinitionException
     */
    public function addContentItemDefinition(ContentItemDefinition $definition)
    {
        if (!is_string($definition->getMimeType())) {
            throw UnregisterableDefinitionException::notAStringMimeType($definition);
        }

        if (!in_array($definition->getMimeType(), ItemType::getList())) {
            throw UnregisterableDefinitionException::unsupportedMimeType($definition);
        }

        if ($this->has($definition->getMimeType())) {
            throw UnregisterableDefinitionException::duplicateMimeType($definition);
        }

        $this->definitions[$definition->getMimeType()] = $definition;
    }

    /**
     * Returns the definition for a specific MIME type, if any.
     *
     * @param string $type
     *
     * @throws UnregisteredDefinitionException
     *
     * @return ItemDefinitionInterface
     */
    public function get($type)
    {
        if (isset($this->definitions[$type])) {
            return $this->definitions[$type];
        }

        throw new UnregisteredDefinitionException(
            $type,
            UnregisteredDefinitionException::TARGET_MIME_TYPE
        );
    }

    /**
     * Checks if a mime-type is supported by the bundle.
     *
     * @param string $type
     *
     * @return bool
     */
    public function has($type)
    {
        return isset($this->definitions[$type]);
    }

    /**
     * Gets the list of supported item mime-types.
     *
     * @return array
     */
    public function getSupportedTypes()
    {
        return array_keys($this->definitions);
    }

    /**
     * Converts mime-type to a supported format.
     *
     * @param string $mimeType
     *
     * @return string
     */
    public function getConvertedType($mimeType)
    {
        $type = $mimeType;

        if (1 !== preg_match('#^application\/x\.[^/]+\+json$#', $mimeType) && 1 === preg_match('#^[^/]+\/[^/]+$#', $mimeType)) {
            $type = 'content';
        }

        return $type;
    }

    /**
     * Checks if the mime-type is a supported question type.
     *
     * @param string $mimeType
     *
     * @return bool
     */
    public function isQuestionType($mimeType)
    {
        return (1 === preg_match('#^application\/x\.[^/]+\+json$#', $mimeType)) && $this->has($mimeType);
    }
}
