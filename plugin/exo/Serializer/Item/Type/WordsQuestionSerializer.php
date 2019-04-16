<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Misc\KeywordSerializer;

/**
 * @DI\Service("ujm_exo.serializer.question_words")
 * @DI\Tag("claroline.serializer")
 */
class WordsQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var KeywordSerializer
     */
    private $keywordSerializer;

    /**
     * WordsQuestionSerializer constructor.
     *
     * @param KeywordSerializer $keywordSerializer
     *
     * @DI\InjectParams({
     *     "keywordSerializer" = @DI\Inject("ujm_exo.serializer.keyword")
     * })
     */
    public function __construct(KeywordSerializer $keywordSerializer)
    {
        $this->keywordSerializer = $keywordSerializer;
    }

    /**
     * Converts a Words question into a JSON-encodable structure.
     *
     * @param OpenQuestion $wordsQuestion
     * @param array        $options
     *
     * @return array
     */
    public function serialize(OpenQuestion $wordsQuestion, array $options = [])
    {
        $serialized = [];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($wordsQuestion, $options);
        }

        return $serialized;
    }

    /**
     * Converts raw data into an Words question entity.
     *
     * @param array        $data
     * @param OpenQuestion $wordsQuestion
     * @param array        $options
     *
     * @return OpenQuestion
     */
    public function deserialize($data, OpenQuestion $wordsQuestion = null, array $options = [])
    {
        if (empty($wordsQuestion)) {
            $wordsQuestion = new OpenQuestion();
        }

        $this->deserializeSolutions($wordsQuestion, $data['solutions'], $options);

        return $wordsQuestion;
    }

    private function serializeSolutions(OpenQuestion $wordsQuestion, array $options = [])
    {
        return array_map(function (Keyword $keyword) use ($options) {
            return $this->keywordSerializer->serialize($keyword, $options);
        }, $wordsQuestion->getKeywords()->toArray());
    }

    /**
     * Deserializes Question solutions (= a collection of keywords).
     *
     * @param OpenQuestion $wordsQuestion
     * @param array        $solutions
     * @param array        $options
     */
    private function deserializeSolutions(OpenQuestion $wordsQuestion, array $solutions, array $options = [])
    {
        $updatedKeywords = $this->keywordSerializer->deserializeCollection($solutions, $wordsQuestion->getKeywords()->toArray(), $options);

        // Replace keywords collection by the updated one
        $wordsQuestion->setKeywords($updatedKeywords);
    }
}
