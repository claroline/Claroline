<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\ClozeQuestion;
use UJM\ExoBundle\Entity\Misc\Hole;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;
use UJM\ExoBundle\Serializer\Misc\KeywordSerializer;

/**
 * @DI\Service("ujm_exo.serializer.question_cloze")
 */
class ClozeQuestionSerializer implements SerializerInterface
{
    /**
     * @var KeywordSerializer
     */
    private $keywordSerializer;

    /**
     * ClozeQuestionSerializer constructor.
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
     * Converts a Cloze question into a JSON-encodable structure.
     *
     * @param ClozeQuestion $clozeQuestion
     * @param array         $options
     *
     * @return \stdClass
     */
    public function serialize($clozeQuestion, array $options = [])
    {
        $questionData = new \stdClass();

        $questionData->text = $clozeQuestion->getText();
        $questionData->holes = $this->serializeHoles($clozeQuestion);
        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $questionData->solutions = $this->serializeSolutions($clozeQuestion, $options);
        }

        return $questionData;
    }

    /**
     * Converts raw data into a Cloze question entity.
     *
     * @param \stdClass     $data
     * @param ClozeQuestion $clozeQuestion
     * @param array         $options
     *
     * @return ClozeQuestion
     */
    public function deserialize($data, $clozeQuestion = null, array $options = [])
    {
        if (empty($clozeQuestion)) {
            $clozeQuestion = new ClozeQuestion();
        }

        $clozeQuestion->setText($data->text);

        $this->deserializeHoles($clozeQuestion, $data->holes, $data->solutions, $options);

        return $clozeQuestion;
    }

    /**
     * Serializes the Question holes.
     *
     * @param ClozeQuestion $clozeQuestion
     *
     * @return array
     */
    private function serializeHoles(ClozeQuestion $clozeQuestion)
    {
        return array_map(function (Hole $hole) {
            $holeData = new \stdClass();
            $holeData->id = $hole->getUuid();
            $holeData->size = $hole->getSize();

            if ($hole->getSelector()) {
                // We want to propose a list of choices
                $holeData->choices = array_map(function (Keyword $keyword) {
                    return $keyword->getText();
                }, $hole->getKeywords()->toArray());
            }

            $placeholder = $hole->getPlaceholder();
            if (!empty($placeholder)) {
                $holeData->placeholder = $placeholder;
            }

            return $holeData;
        }, $clozeQuestion->getHoles()->toArray());
    }

    /**
     * Deserializes Question holes.
     *
     * @param ClozeQuestion $clozeQuestion
     * @param array         $holes
     * @param array         $solutions
     * @param array         $options
     */
    private function deserializeHoles(ClozeQuestion $clozeQuestion, array $holes, array $solutions, array $options = [])
    {
        $holeEntities = $clozeQuestion->getHoles()->toArray();

        foreach ($holes as $holeData) {
            $hole = null;

            // Searches for an existing hole entity.
            foreach ($holeEntities as $entityIndex => $entityHole) {
                /** @var Hole $entityHole */
                if ($entityHole->getUuid() === $holeData->id) {
                    $hole = $entityHole;
                    unset($holeEntities[$entityIndex]);
                    break;
                }
            }

            $hole = $hole ?: new Hole();
            $hole->setUuid($holeData->id);

            if (!empty($holeData->size)) {
                $hole->setSize($holeData->size);
            }

            if (!empty($holeData->choices)) {
                $hole->setSelector(true);
            } else {
                $hole->setSelector(false);
            }

            foreach ($solutions as $solution) {
                if ($solution->holeId === $holeData->id) {
                    $this->deserializeHoleKeywords($hole, $solution->answers, $options);

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
     *
     * @param Hole  $hole
     * @param array $keywords
     * @param array $options
     */
    private function deserializeHoleKeywords(Hole $hole, array $keywords, array $options = [])
    {
        $updatedKeywords = $this->keywordSerializer->deserializeCollection($keywords, $hole->getKeywords()->toArray(), $options);
        $hole->setKeywords($updatedKeywords);
    }

    private function serializeSolutions(ClozeQuestion $clozeQuestion, array $options = [])
    {
        return array_map(function (Hole $hole) use ($options) {
            $solutionData = new \stdClass();
            $solutionData->holeId = $hole->getUuid();

            $solutionData->answers = array_map(function (Keyword $keyword) use ($options) {
                return $this->keywordSerializer->serialize($keyword, $options);
            }, $hole->getKeywords()->toArray());

            return $solutionData;
        }, $clozeQuestion->getHoles()->toArray());
    }
}
