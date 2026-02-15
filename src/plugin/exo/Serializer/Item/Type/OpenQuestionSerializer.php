<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Library\Options\Transfer;

class OpenQuestionSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'exo_question_open';
    }

    public function serialize(OpenQuestion $openQuestion, array $options = []): array
    {
        $serialized = [
            'contentType' => $openQuestion->getContentType(),
            'maxLength' => $openQuestion->getAnswerMaxLength(),
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = [];
        }

        return $serialized;
    }

    public function deserialize(array $data, ?OpenQuestion $openQuestion = null, array $options = []): OpenQuestion
    {
        if (empty($openQuestion)) {
            $openQuestion = new OpenQuestion();
        }
        $this->sipe('maxLength', 'setAnswerMaxLength', $data, $openQuestion);
        $this->sipe('contentType', 'setContentType', $data, $openQuestion);

        return $openQuestion;
    }
}
