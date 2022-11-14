<?php

namespace UJM\ExoBundle\Serializer\Attempt;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Library\Options\Score;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Serializer for paper data.
 */
class PaperSerializer
{
    use SerializerTrait;

    /**
     * @var UserSerializer
     */
    private $userSerializer;

    /**
     * @var AnswerSerializer
     */
    private $answerSerializer;

    public function __construct(UserSerializer $userSerializer, AnswerSerializer $answerSerializer)
    {
        $this->userSerializer = $userSerializer;
        $this->answerSerializer = $answerSerializer;
    }

    public function getName()
    {
        return 'exo_paper';
    }

    /**
     * Converts a Paper into a JSON-encodable structure.
     */
    public function serialize(Paper $paper, array $options = []): array
    {
        $serialized = [
            'id' => $paper->getUuid(),
            'number' => $paper->getNumber(),
            'finished' => !$paper->isInterrupted(),
            'user' => $paper->getUser() && !$paper->isAnonymized() ? $this->userSerializer->serialize($paper->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'startDate' => $paper->getStart() ? DateNormalizer::normalize($paper->getStart()) : null,
            'endDate' => $paper->getEnd() ? DateNormalizer::normalize($paper->getEnd()) : null,
            'total' => $paper->getTotal(),
        ];

        // Adds detail information
        if (!in_array(Transfer::MINIMAL, $options)) {
            $serialized['structure'] = $paper->getStructure(true);
            $serialized['answers'] = $this->serializeAnswers($paper, $options);
        }

        // Adds user score
        if (in_array(Transfer::INCLUDE_USER_SCORE, $options)) {
            $score = $paper->getScore();
            if ($score) {
                $score = round($score, Score::PRECISION);
            }
            $serialized['score'] = $score;
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Paper entity.
     *
     * @param array $data
     * @param Paper $paper
     *
     * @return Paper
     */
    public function deserialize($data, Paper $paper = null, array $options = [])
    {
        $paper = $paper ?: new Paper();

        $this->sipe('id', 'setUuid', $data, $paper);
        $this->sipe('number', 'setNumber', $data, $paper);
        $this->sipe('score', 'setScore', $data, $paper);

        if (isset($data['startDate'])) {
            $startDate = DateNormalizer::denormalize($data['startDate']);
            $paper->setStart($startDate);
        }
        if (isset($data['endDate'])) {
            $endDate = DateNormalizer::denormalize($data['endDate']);
            $paper->setEnd($endDate);
        }
        if (isset($data['structure'])) {
            $paper->setStructure(json_encode($data['structure']));
        }
        if (isset($data['finished'])) {
            $paper->setInterrupted(!$data['finished']);
        }
        if (isset($data['answers'])) {
            $this->deserializeAnswers($paper, $data['answers'], $options);
        }

        return $paper;
    }

    /**
     * Serializes paper answers.
     *
     * @return array
     */
    private function serializeAnswers(Paper $paper, array $options = [])
    {
        // We need to inject the hints available in the structure
        $options['hints'] = $paper->getHints();

        return array_map(function (Answer $answer) use ($options) {
            return $this->answerSerializer->serialize($answer, $options);
        }, $paper->getAnswers()->toArray());
    }

    private function deserializeAnswers(Paper $paper, array $answers, array $options = [])
    {
        foreach ($answers as $answerData) {
            $answer = $this->answerSerializer->deserialize($answerData, $paper->getAnswer($answerData['questionId']), $options);
            $paper->addAnswer($answer);
        }
    }
}
