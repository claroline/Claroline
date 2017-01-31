<?php

namespace UJM\ExoBundle\Library\Question;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Question\Definition\Exception\UnregisterableDefinitionException;
use UJM\ExoBundle\Library\Question\Definition\Exception\UnregisteredDefinitionException;
use UJM\ExoBundle\Library\Question\Definition\QuestionDefinitionInterface;

/**
 * Collects definition class for each Question type defined.
 *
 * @DI\Service("ujm_exo.collection.question_definitions")
 */
class QuestionDefinitionsCollection
{
    /**
     * The list of registered question definitions.
     *
     * @var QuestionDefinitionInterface[]
     */
    private $definitions = [];

    /**
     * Adds a question definition to the collection.
     *
     * @param QuestionDefinitionInterface $definition
     *
     * @throws UnregisterableDefinitionException
     */
    public function addDefinition(QuestionDefinitionInterface $definition)
    {
        if (!is_string($definition->getMimeType())) {
            throw UnregisterableDefinitionException::notAStringMimeType($definition);
        }

        if (!in_array($definition->getMimeType(), QuestionType::getList())) {
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
     * @return QuestionDefinitionInterface
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
     * Gets the list of supported question mime-types.
     *
     * @return array
     */
    public function getSupportedTypes()
    {
        return array_keys($this->definitions);
    }
}
