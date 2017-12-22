<?php

namespace UJM\ExoBundle\Library\Options;

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
     * Adds administrations info in the serialized data.
     *
     * @var string
     */
    const INCLUDE_ADMIN_META = 'includeAdminMeta';

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
    const NO_FETCH = 'noFetch';

    /**
     * Persist the tags of the question.
     *
     * @var string
     */
    const PERSIST_TAG = 'persistTag';
}
