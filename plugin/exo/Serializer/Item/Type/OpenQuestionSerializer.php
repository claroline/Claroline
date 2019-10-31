<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Library\Options\Transfer;

class OpenQuestionSerializer
{
    use SerializerTrait;

    /**
     * Converts a Open question into a JSON-encodable structure.
     *
     * @param OpenQuestion $openQuestion
     * @param array        $options
     *
     * @return array
     */
    public function serialize(OpenQuestion $openQuestion, array $options = [])
    {
        $serialized = [
            'contentType' => 'text',
            'maxLength' => $openQuestion->getAnswerMaxLength(),
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = [];
        }

        return $serialized;
    }

    public function getName()
    {
        return 'exo_question_open';
    }

    /**
     * Converts raw data into an Open question entity.
     *
     * @param array        $data
     * @param OpenQuestion $openQuestion
     * @param array        $options
     *
     * @return OpenQuestion
     */
    public function deserialize($data, OpenQuestion $openQuestion = null, array $options = [])
    {
        if (empty($openQuestion)) {
            $openQuestion = new OpenQuestion();
        }
        $this->sipe('maxLength', 'setAnswerMaxLength', $data, $openQuestion);

        return $openQuestion;
    }
}
