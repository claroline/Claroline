<?php

namespace UJM\ExoBundle\Transfer\Json;

use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;

interface QuestionHandlerInterface
{
    /**
     * Returns the supported MIME type (e.g. "application/x.choice+json").
     *
     * @return string
     */
    function getQuestionMimeType();

    /**
     * Returns the supported interaction type (e.g. "InteractionQCM").
     *
     * @return string
     */
    function getInteractionType();

    /**
     * Returns the JSON schema URI (e.g. "http://json-schema.org/qcm/schema.json").
     *
     * @return string
     */
    function getJsonSchemaUri();

    /**
     * Performs any validation task not feasible using solely the JSONSchema
     * validator. Returns an array of validation errors, if any.
     *
     * @param \stdClass $questionData
     * @return array
     */
    function validateAfterSchema(\stdClass $questionData);

    /**
     * Converts the interaction details of a JSON-decoded question to
     * their corresponding entities and persists them (without flushing).
     *
     * This method is called when importing a question in JSON format. It
     * is passed the question entity which will be flushed at the end of
     * the import process, as well as the JSON-decoded data to import. Its
     * role is to handle anything specific to the supported question type.
     *
     * @param Question  $question
     * @param \stdClass $importData
     */
    function persistInteractionDetails(Question $question, \stdClass $importData);

    /**
     * Converts the interaction details of a question to a standard object
     * representation ready to be JSON-encoded.
     *
     * This method is called when exporting a question in JSON format. It
     * is passed the question entity being exported as well as its standard
     * object representation. Its role is to augment the latter with everything
     * specific to the supported question type.
     *
     * If the third parameter is set to true, the conversion process will
     * include any data related to the solution of the question.
     *
     * @param Question  $question
     * @param \stdClass $exportData
     * @param bool      $withSolution
     */
    function convertInteractionDetails(Question $question, \stdClass $exportData, $withSolution = true);

    /**
     * Converts the details of an answer to a representation ready to
     * be JSON-encoded.
     *
     * @param Response $response
     * @return mixed
     */
    function convertAnswerDetails(Response $response);

    /**
     * Ensures answer data is in the correct format and returns validation
     * errors, if any.
     *
     * @param Question  $question
     * @param mixed     $data
     * @return array
     */
    function validateAnswerFormat(Question $question, $data);

    /**
     * Populates an answer with its actual contents and mark.
     *
     * @param Question  $question
     * @param Response  $response
     * @param mixed     $data
     */
    function storeAnswerAndMark(Question $question, Response $response, $data);
}
