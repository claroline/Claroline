<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Misc;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * Validates Keyword data.
 *
 * @DI\Service("ujm_exo.validator.keyword")
 */
class KeywordValidator extends JsonSchemaValidator
{
    /**
     * {@inheritdoc}
     */
    public function getJsonSchemaUri()
    {
        return 'misc/keyword/schema.json';
    }

    /**
     * {@inheritdoc}
     */
    public function validateAfterSchema($keyword, array $options = [])
    {
        return [];
    }

    /**
     * Validates a collection of Keywords.
     * Checks :
     *  - There is no more than one keyword with the same text and case sensitiveness
     *  - There is at least one keyword with a positive score.
     *
     * @param array $keywords
     * @param array $options
     *
     * @return array
     */
    public function validateCollection(array $keywords, array $options = [])
    {
        $errors = [];

        $maxScore = -1;
        $keywordsData = []; // We will store text and caseSensitive to check there is no duplicate
        foreach ($keywords as $index => $keyword) {
            // Validate keyword
            $errors = array_merge($errors, $this->validateAfterSchema($keyword, $options));

            if (empty($errors)) {
                if ($keyword->score > $maxScore) {
                    $maxScore = $keyword->score;
                }

                // Checks for duplicates
                if (isset($keywordsData[$keyword->text]) && in_array($keyword->caseSensitive, $keywordsData[$keyword->text], true)) {
                    // Keywords already exists
                    $caseSensitive = $keyword->caseSensitive ? 'true' : 'false'; // Converts to string to display in logs
                    $errors[] = [
                        'path' => "/{$index}",
                        'message' => "there is already a keyword with text: '{$keyword->text}' and caseSensitive: '{$caseSensitive}'",
                    ];
                } else {
                    $keywordsData[$keyword->text] = [];
                    $keywordsData[$keyword->text][] = $keyword->caseSensitive;
                }
            }
        }

        // check there is a keyword with a positive score
        if (in_array(Validation::VALIDATE_SCORE, $options) && $maxScore <= 0) {
            $errors[] = [
                'path' => '',
                'message' => 'there is no keyword with a positive score',
            ];
        }

        return $errors;
    }
}
