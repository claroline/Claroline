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
    public function convertInteractionDetails(Question $question, \stdClass $exportData, $withSolution = true, $forPaperList = false)
    {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionHole');
        $holeQuestion = $repo->findOneBy(['question' => $question]);
        $holes = $holeQuestion->getHoles()->toArray();
        $text = $holeQuestion->getHtmlWithoutValue();
        
        $scoreTotal = 0;
        foreach ($holes as $hole) {
            $maxScore = 0;
            foreach ($hole->getWordResponses() as $wd) {
                if ($wd->getScore() > $maxScore) {
                    $maxScore = $wd->getScore();
                }
            }
            $scoreTotal = $scoreTotal + $maxScore;
        }
        
        $exportData->scoreTotal = $scoreTotal;
        $exportData->text = $text;
        if ($withSolution) {
            $exportData->solution = $holeQuestion->getHtml();
            $exportData->solutions = array_map(function ($hole) {
                $solutionData = new \stdClass();
                $solutionData->id = (string) $hole->getId();

                $wordResponses = $hole->getWordResponses()->toArray();
                $expectedWord = null;
                array_walk($wordResponses, function ($wr) use (&$expectedWord) {
                    if (empty($expectedWord) || ($wr->getScore() > $expectedWord->getScore())) {
                        $expectedWord = $wr;
                    }
                });

                $solutionData->wordResponses = array_map(function ($wr) use ($expectedWord) {
                    $wrData = new \stdClass();
                    $wrData->id = (string) $wr->getId();
                    $wrData->response = (string) $wr->getResponse();
                    $wrData->caseSensitive = $wr->getCaseSensitive();
                    $wrData->score = $wr->getScore();
                    $wrData->feedback = $wr->getFeedback();
                    $wrData->rightResponse = $expectedWord->getId() === $wr->getId();

                    return $wrData;
                }, $wordResponses);

                return $solutionData;
            }, $holes);
        }

        $exportData->holes = array_map(function ($hole) {
            $holeData = new \stdClass();
            $holeData->id = (string) $hole->getId();
            $holeData->type = 'text/html';
            $holeData->selector = $hole->getSelector();
            $holeData->position = (string) $hole->getPosition();
            return $holeData;
        }, $holes);
        
        return $exportData;
    }
    
    public function convertQuestionAnswers(Question $question, \stdClass $exportData){
        $repo = $this->om->getRepository('UJMExoBundle:InteractionHole');
        $holeQuestion = $repo->findOneBy(['question' => $question]);
        
        $holes = $holeQuestion->getHoles()->toArray();
        $exportData->solutions = array_map(function ($hole) {
                $solutionData = new \stdClass();
                $solutionData->id = (string) $hole->getId();
                $solutionData->type = 'text/html';
                $solutionData->selector = $hole->getSelector();
                $solutionData->position = (string) $hole->getPosition();

                $wordResponses = $hole->getWordResponses()->toArray();
                $expectedWord = null;
                array_walk($wordResponses, function ($wr) use (&$expectedWord) {
                    if (empty($expectedWord) || ($wr->getScore() > $expectedWord->getScore())) {
                        $expectedWord = $wr;
                    }
                });

                $solutionData->wordResponses = array_map(function ($wr) use ($expectedWord) {
                    $wrData = new \stdClass();
                    $wrData->id = (string) $wr->getId();
                    $wrData->response = (string) $wr->getResponse();
                    $wrData->score = $wr->getScore();
                    $wrData->rightResponse = $expectedWord->getId() === $wr->getId();
                    if ($wr->getFeedback()) {
                        $wrData->feedback = $wr->getFeedback();
                    }

                    return $wrData;
                }, $hole->getWordResponses()->toArray());

                return $solutionData;
            }, $holes);
        return $exportData;
    }

    /**
     * {@inheritdoc}
     */
    public function convertAnswerDetails(Response $response)
    {
        $parts = json_decode($response->getResponse());
        
        foreach ($parts as $key=>$value) {
            $array[$key] = $value;
        }
        
    //    $parts = explode(';', $response->getResponse());

        return array_filter($array, function ($part) {
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

        foreach ($data as $answer) {
            if ($answer || $answer !== null) {
                if (empty($answer['holeId'])) {
                    return ['Answer `holeId` cannot be empty'];
                }

                if (!is_string($answer['holeId'])) {
                    return ['Answer `holeId` must contain only strings , ' . gettype($answer['holeId']) . ' given.'];
                }

                if (!in_array($answer['holeId'], $holeIds)) {
                    return ['Answer array identifiers must reference question holes'];
                }

                if (!empty($answer['answerText']) && !is_string($answer['answerText'])) {
                    return ['Answer `answerText` must contain only strings , ' . gettype($answer['holeId']) . ' given.'];
                }
            }
        }

        return [];
    }

    /**
     * @todo handle global score option
     * @todo threat Hole with select and those with input in the same way (for select, we use ID and we need to use the Word text instead)
     *
     * {@inheritdoc}
     */
    public function storeAnswerAndMark(Question $question, Response $response, $data)
    {
        $interaction = $this->om->getRepository('UJMExoBundle:InteractionHole')
            ->findOneByQuestion($question);

        $mark = 0;

        foreach ($data as $answer) {
            foreach ($interaction->getHoles() as $hole) {
                foreach ($hole->getWordResponses() as $wd) {
                    if ($hole->getSelector() === true) {
                        if ((string)$wd->getId() === (string)$answer['answerText']) {
                            $mark += $wd->getScore();
                        }
                    }
                    else {
                        if ( (!$wd->getCaseSensitive() && $wd->getResponse() === $answer['answerText'])
                            || ($wd->getCaseSensitive() && strtolower($wd->getResponse()) === strtolower($answer['answerText'])) ) {
                            $mark += $wd->getScore();
                        }
                    }
                }
            }
        }
        
        $answers = [];
        $i=0;
        foreach ($data as $answer) {
            if ($answer || $answer !== null) {
                $answers[$i] = $answer;
            }
            $i++;
        }

        if ($mark < 0) {
            $mark = 0;
        }
        
        $json = json_encode($answers);
        $response->setResponse($json);
        $response->setMark($mark);
    }
}
