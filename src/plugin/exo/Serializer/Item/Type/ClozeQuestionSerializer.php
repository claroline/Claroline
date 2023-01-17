<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\ClozeQuestion;
use UJM\ExoBundle\Entity\Misc\Hole;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Misc\KeywordSerializer;

class ClozeQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var KeywordSerializer
     */
    private $keywordSerializer;

    /**
     * ClozeQuestionSerializer constructor.
     */
    public function __construct(KeywordSerializer $keywordSerializer)
    {
        $this->keywordSerializer = $keywordSerializer;
    }

    public function getName()
    {
        return 'exo_question_cloze';
    }

    /**
     * Converts a Cloze question into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(ClozeQuestion $clozeQuestion, array $options = [])
    {
        $serialized = [
            'text' => $clozeQuestion->getText(),
            'holes' => $this->serializeHoles($clozeQuestion, $options),
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($clozeQuestion, $options);
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Cloze question entity.
     *
     * @param array         $data
     * @param ClozeQuestion $clozeQuestion
     *
     * @return ClozeQuestion
     */
    public function deserialize($data, ClozeQuestion $clozeQuestion = null, array $options = [])
    {
        if (empty($clozeQuestion)) {
            $clozeQuestion = new ClozeQuestion();
        }
        $this->sipe('text', 'setText', $data, $clozeQuestion);

        $this->deserializeHoles($clozeQuestion, $data['holes'], $data['solutions'], $options);

        return $clozeQuestion;
    }

    /**
     * Serializes the Question holes.
     */
    private function serializeHoles(ClozeQuestion $clozeQuestion, ?array $options = []): ?array
    {
        return array_values(array_map(function (Hole $hole) use ($options) {
            $holeData = [
                'id' => $hole->getUuid(),
                'size' => $hole->getSize(),
                'random' => $hole->getShuffle(),
            ];

            if ($hole->getSelector()) {
                // We want to propose a list of choices
                $choices = array_map(function (Keyword $keyword) {
                    return $keyword->getText();
                }, $hole->getKeywords()->toArray());

                if ($hole->getShuffle() && in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
                    shuffle($choices);
                }

                $holeData['choices'] = $choices;
            }

            $placeholder = $hole->getPlaceholder();

            if (!empty($placeholder)) {
                $holeData['placeholder'] = $placeholder;
            }

            return $holeData;
        }, $clozeQuestion->getHoles()->toArray()));
    }

    /**
     * Deserializes Question holes.
     */
    private function deserializeHoles(ClozeQuestion $clozeQuestion, array $holes, array $solutions, array $options = [])
    {
        $holeEntities = $clozeQuestion->getHoles()->toArray();

        foreach ($holes as $holeData) {
            $hole = null;

            // Searches for an existing hole entity.
            foreach ($holeEntities as $entityIndex => $entityHole) {
                /** @var Hole $entityHole */
                if ($entityHole->getUuid() === $holeData['id']) {
                    $hole = $entityHole;
                    unset($holeEntities[$entityIndex]);
                    break;
                }
            }

            $hole = $hole ?: new Hole();
            $hole->setUuid($holeData['id']);

            if (!empty($holeData['size'])) {
                $hole->setSize($holeData['size']);
            }

            if (!empty($holeData['choices'])) {
                $hole->setSelector(true);
                $hole->setShuffle(!empty($holeData['random']));
            } else {
                $hole->setSelector(false);
                $hole->setShuffle(false);
            }

            foreach ($solutions as $solution) {
                if ($solution['holeId'] === $holeData['id']) {
                    $this->deserializeHoleKeywords($hole, $solution['answers'], $options);

                    break;
                }
            }

            $clozeQuestion->addHole($hole);
        }

        // Remaining holes are no longer in the Question
        foreach ($holeEntities as $holeToRemove) {
            $clozeQuestion->removeHole($holeToRemove);
        }
    }

    /**
     * Deserializes the keywords of a Hole.
     */
    private function deserializeHoleKeywords(Hole $hole, array $keywords, array $options = [])
    {
        $updatedKeywords = $this->keywordSerializer->deserializeCollection($keywords, $hole->getKeywords()->toArray(), $options);
        $hole->setKeywords($updatedKeywords);
    }

    private function serializeSolutions(ClozeQuestion $clozeQuestion, array $options = [])
    {
        return array_values(array_map(function (Hole $hole) use ($options) {
            return [
                'holeId' => $hole->getUuid(),
                'answers' => array_values(array_map(function (Keyword $keyword) use ($options) {
                    return $this->keywordSerializer->serialize($keyword, $options);
                }, $hole->getKeywords()->toArray())),
            ];
        }, $clozeQuestion->getHoles()->toArray()));
    }
}
