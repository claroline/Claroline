<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Misc\KeywordSerializer;

class WordsQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var KeywordSerializer
     */
    private $keywordSerializer;

    /**
     * WordsQuestionSerializer constructor.
     */
    public function __construct(KeywordSerializer $keywordSerializer)
    {
        $this->keywordSerializer = $keywordSerializer;
    }

    public function getName()
    {
        return 'exo_question_words';
    }

    /**
     * Converts a Words question into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(OpenQuestion $wordsQuestion, array $options = [])
    {
        $serialized = [
            'contentType' => $wordsQuestion->getContentType(),
        ];

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
     *
     * @return OpenQuestion
     */
    public function deserialize($data, OpenQuestion $wordsQuestion = null, array $options = [])
    {
        if (empty($wordsQuestion)) {
            $wordsQuestion = new OpenQuestion();
        }

        $this->sipe('contentType', 'setContentType', $data, $wordsQuestion);

        $this->deserializeSolutions($wordsQuestion, $data['solutions'], $options);

        return $wordsQuestion;
    }

    private function serializeSolutions(OpenQuestion $wordsQuestion, array $options = [])
    {
        return array_values(array_map(function (Keyword $keyword) use ($options) {
            return $this->keywordSerializer->serialize($keyword, $options);
        }, $wordsQuestion->getKeywords()->toArray()));
    }

    /**
     * Deserializes Question solutions (= a collection of keywords).
     */
    private function deserializeSolutions(OpenQuestion $wordsQuestion, array $solutions, array $options = [])
    {
        $updatedKeywords = $this->keywordSerializer->deserializeCollection($solutions, $wordsQuestion->getKeywords()->toArray(), $options);

        // Replace keywords collection by the updated one
        $wordsQuestion->setKeywords($updatedKeywords);
    }
}
