<?php

namespace UJM\ExoBundle\Transfer\Json;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("ujm.exo.question_handler_collector")
 */
class QuestionHandlerCollector
{
    /**
     * @var QuestionHandlerInterface[]
     */
    private $handlers = [];

    /**
     * Adds a question handler to the collection.
     *
     * @param QuestionHandlerInterface $handler
     *
     * @throws UnregisterableHandlerException
     */
    public function addHandler(QuestionHandlerInterface $handler)
    {
        if (!is_string($handler->getQuestionMimeType())) {
            throw UnregisterableHandlerException::notAStringMimeType($handler);
        }

        if (!is_string($handler->getInteractionType())) {
            throw UnregisterableHandlerException::notAStringInteractionType($handler);
        }

        if (!is_string($handler->getJsonSchemaUri())) {
            throw UnregisterableHandlerException::notAStringSchemaUri($handler);
        }

        if ($this->hasHandlerForMimeType($handler->getQuestionMimeType())) {
            throw UnregisterableHandlerException::duplicateMimeType($handler);
        }

        if ($this->hasHandlerForInteractionType($handler->getInteractionType())) {
            throw UnregisterableHandlerException::duplicateInteractionType($handler);
        }

        if ($this->hasHandlerForSchemaUri($handler->getJsonSchemaUri())) {
            throw UnregisterableHandlerException::duplicateSchemaUri($handler);
        }

        $this->handlers[$handler->getQuestionMimeType()] = $handler;
    }

    /**
     * Returns the handler for a specific MIME type, if any.
     *
     * @param string $type
     *
     * @throws UnregisteredHandlerException
     *
     * @return QuestionHandlerInterface
     */
    public function getHandlerForMimeType($type)
    {
        if (isset($this->handlers[$type])) {
            return $this->handlers[$type];
        }

        throw new UnregisteredHandlerException(
            $type,
            UnregisteredHandlerException::TARGET_MIME_TYPE
        );
    }

    /**
     * Returns the handler for a specific interaction type, if any.
     *
     * @param string $type
     *
     * @throws UnregisteredHandlerException
     *
     * @return QuestionHandlerInterface
     */
    public function getHandlerForInteractionType($type)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->getInteractionType() === $type) {
                return $handler;
            }
        }

        throw new UnregisteredHandlerException(
            $type,
            UnregisteredHandlerException::TARGET_INTERACTION
        );
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasHandlerForMimeType($type)
    {
        return isset($this->handlers[$type]);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasHandlerForInteractionType($type)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->getInteractionType() === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $uri
     *
     * @return bool
     */
    public function hasHandlerForSchemaUri($uri)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->getInteractionType() === $uri) {
                return true;
            }
        }

        return false;
    }
}
