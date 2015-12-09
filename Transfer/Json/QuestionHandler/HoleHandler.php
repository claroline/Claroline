<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Hole;
use UJM\ExoBundle\Entity\InteractionHole;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerInterface;

/**
 * @DI\Service("ujm.exo.hole_handler")
 * @DI\Tag("ujm.exo.question_handler")
 */
class HoleHandler implements QuestionHandlerInterface
{
    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionMimeType()
    {
        return 'application/x.cloze+json';
    }

    /**
     * {@inheritdoc}
     */
    public function getInteractionType()
    {
        return InteractionHole::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonSchemaUri()
    {
        return 'http://json-quiz.github.io/json-quiz/schemas/question/cloze/schema.json';
    }

    /**
     * {@inheritdoc}
     */
    public function validateAfterSchema(\stdClass $questionData)
    {
        $errors = [];

        if (!isset($questionData->solutions)) {
            return $errors;
        }

        // check solution ids are consistent with choice ids
        $holeIds = array_map(function ($hole) {
            return $hole->id;
        }, $questionData->holes);

        foreach ($questionData->solutions as $index => $solution) {
            if (!in_array($solution->id, $holeIds)) {
                $errors[] = [
                    'path' => "solutions[{$index}]",
                    'message' => "id {$solution->id} doesn't match any choice id"
                ];
            }
        }

        // check there is a positive score solution
        $maxScore = -1;

        foreach ($questionData->solutions as $solution) {
            if ($solution->score > $maxScore) {
                $maxScore = $solution->score;
            }
        }

        if ($maxScore <= 0) {
            $errors[] = [
                'path' => 'solutions',
                'message' => 'there is no solution with a positive score'
            ];
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function persistInteractionDetails(Question $question, \stdClass $importData)
    {
        $interaction = new InteractionHole();

        for ($i = 0, $max = count($importData->holes); $i < $max; ++$i) {
            // temporary limitation
            if ($importData->holes[$i]->type !== 'text/html') {
                throw new \Exception(
                    "Import not implemented for MIME type {$importData->holes[$i]->type}"
                );
            }

            $hole = new Hole();
        //    $hole->setLabel($importData->holes[$i]->data);
            $hole->setOrdre($i);

            $hole->setInteractionHole($interaction);
            $interaction->addHole($hole);
            $this->om->persist($hole);
        }

        $interaction->setQuestion($question);
        $this->om->persist($interaction);
    }

    /**
     * {@inheritdoc}
     */
    public function convertInteractionDetails(Question $question, \stdClass $exportData, $withSolution = true)
    {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionHole');
        $holeQuestion = $repo->findOneBy(['question' => $question]);
        $holes = $holeQuestion->getHoles()->toArray();
        $text = $holeQuestion->getHtmlWithoutValue();

        $exportData->text = $text;
        $exportData->holes = array_map(function ($hole) {
            $holeData = new \stdClass();
            $holeData->id = (string) $hole->getId();
            $holeData->type = 'text/html';
            $holeData->wordResponses = array_map(function ($wr) {
                $wrData = new \stdClass();
                $wrData->id = (string) $wr->getId();
                $wrData->score = $wr->getScore();
                return $wrData;
            }, $hole->getWordResponses()->toArray());

            return $holeData;
        }, $holes);
        
        return $exportData;
    }

    /**
     * {@inheritdoc}
     */
    public function convertAnswerDetails(Response $response)
    {
        $parts = explode(';', $response->getResponse());

        return array_filter($parts, function ($part) {
            return $part !== '';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function validateAnswerFormat(Question $question, $data)
    {
        if (!is_array($data)) {
            return ['Answer data must be an array, ' . gettype($data) . ' given'];
        }
        $count = 0;

        if (0 === $count = count($data)) {
            return ['Answer data cannot be empty'];
        }

        $interaction = $this->om->getRepository('UJMExoBundle:InteractionHole')
            ->findOneByQuestion($question);
        $holeIds = array_map(function ($hole) {
            return (string) $hole->getId();
        }, $interaction->getHoles()->toArray());

        foreach ($data as $id) {
            if (!is_string($id)) {
                return ['Answer array must contain only string identifiers'];
            }

            if (!in_array($id, $holeIds)) {
                return ['Answer array identifiers must reference question choices'];
            }
        }

        return [];
    }

    /**
     * @todo handle global score option
     *
     * {@inheritdoc}
     */
    public function storeAnswerAndMark(Question $question, Response $response, $data)
    {
        $interaction = $this->om->getRepository('UJMExoBundle:InteractionHole')
            ->findOneByQuestion($question);

        if (!$interaction->getWeightResponse()) {
            throw new \Exception('Global score not implemented yet');
        }

        $mark = 0;
/*
        foreach ($interaction->getHoles() as $hole) {
            if (in_array((string) $hole->getId(), $data)) {
                $mark += $hole->getWeight();
            }
        }*/

        if ($mark < 0) {
            $mark = 0;
        }

        $response->setResponse(implode(';', $data) . ';');
        $response->setMark($mark);
    }
}
