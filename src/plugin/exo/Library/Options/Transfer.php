<?php

namespace UJM\ExoBundle\Library\Options;

use Claroline\AppBundle\API\Options as ApiOptions;

/**
 * Defines Serializers options.
 */
final class Transfer
{
    /**
     * Only serializes minimal data of the Entity.
     *
     * @var string
     */
    const MINIMAL = 'minimal';

    /**
     * Adds solutions info in the serialized data.
     *
     * @var string
     */
    const INCLUDE_SOLUTIONS = 'includeSolutions';

    /**
     * Adds user scores in the serialized data.
     *
     * @var string
     */
    const INCLUDE_USER_SCORE = 'includeUserScore';

    /**
     * Applies shuffle to the answers of a question.
     *
     * @var string
     */
    const SHUFFLE_ANSWERS = 'shuffleAnswers';

    /**
     * Avoids data fetch from DB.
     *
     * Not really aesthetic, this permits to force the recreation
     * of shared entities (eg. Items, Categories) for importing/copying quizzes
     *
     * @var string
     */
    const NO_FETCH = 'no_fetch';

    /**
     * Persist the tags of the question.
     *
     * @var string
     */
    const PERSIST_TAG = 'persistTag';

    /**
     * Refrest UUID for steps and questions before persisting.
     *
     * @var string
     */
    const REFRESH_UUID = ApiOptions::REFRESH_UUID;
}
