<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\Document;
use UJM\ExoBundle\Entity\InteractionGraphic;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerInterface;

/**
 * @DI\Service("ujm.exo.graphic_handler")
 * @DI\Tag("ujm.exo.question_handler")
 */
class GraphicHandler implements QuestionHandlerInterface {

    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om) {
        $this->om = $om;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionMimeType() {
        return 'application/x.graphic+json';
    }

    /**
     * {@inheritdoc}
     */
    public function getInteractionType() {
        return InteractionGraphic::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonSchemaUri() {
        return 'http://json-quiz.github.io/json-quiz/schemas/question/graphic/schema.json';
    }

    /**
     * {@inheritdoc}
     */
    public function validateAfterSchema(\stdClass $questionData) {
        $errors = [];

        if (!isset($questionData->solutions)) {
            return $errors;
        }

        // check solution ids are consistent with coords ids
        $coordIds = array_map(function ($coord) {
            return $coord->id;
        }, $questionData->coords);

        foreach ($questionData->solutions as $index => $solution) {
            if (!in_array($solution->id, $coordIds)) {
                $errors[] = [
                    'path' => "solutions[{$index}]",
                    'message' => "id {$solution->id} doesn't match any coord id"
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
    public function persistInteractionDetails(Question $question, \stdClass $importData) {
        $interaction = new InteractionGraphic();

        for ($i = 0, $max = count($importData->coords); $i < $max; ++$i) {

            $coord = new Coords();

            foreach ($importData->solutions as $solution) {
                if ($solution->id === $importData->choices[$i]->id) {
                    $coord->setValue($solution->value);
                    $coord->setShape($solution->shape);
                    $coord->setScoreCoords($solution->score);
                    $coord->setSize($solution->size);
                    if (isset($solution->feedback)) {
                        $coord->setFeedback($solution->feedback);
                    }
                    // should be required ?
                    if (isset($solution->color)) {
                        $coord->setColor($solution->color);
                    } else {
                        $coord->setColor('white');
                    }
                }
            }

            $coord->setInteractionGraphic($interaction);
            $interaction->addCoord($coord);
            $this->om->persist($coord);
        }

        // should we upload the document ??
        $document = new Document();
        $document->setLabel($importData->document->label ? $importData->document->label : '');
        $document->setUrl($importData->document->url);
        $ext = pathinfo($importData->document->url)['extension'];
        $document->setType($ext);
        $this->om->persist($document);

        $interaction->setWidth($importData->width);
        $interaction->setHeight($importData->height);
        $interaction->setDocument($document);

        $interaction->setQuestion($question);
        $this->om->persist($interaction);
    }

    /**
     * {@inheritdoc}
     */
    public function convertInteractionDetails(Question $question, \stdClass $exportData, $withSolution = true, $forPaperList = false) {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionGraphic');
        $graphic = $repo->findOneBy(['question' => $question]);
        $coords = $graphic->getCoords()->toArray();

        $exportData->coords = array_map(function ($coord) {
            $coordData = new \stdClass();
            $coordData->id = (string) $coord->getId();
            return $coordData;
        }, $coords);

        if ($withSolution) {
            $exportData->solutions = array_map(function ($coord) {
                $solutionData = new \stdClass();
                $solutionData->id = (string) $coord->getId();
                $solutionData->value = $coord->getValue();
                $solutionData->shape = $coord->getShape();
                $solutionData->color = $coord->getColor();
                $solutionData->size = $coord->getSize();
                $solutionData->score = $coord->getScore();
                if ($coord->getFeedback()) {
                    $solutionData->feedback = $coord->getFeedback();
                }

                return $solutionData;
            }, $coords);
        }

        return $exportData;
    }

    public function convertQuestionAnswers(Question $question, \stdClass $exportData) {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionGraphic');
        $graphic = $repo->findOneBy(['question' => $question]);
        $coords = $graphic->getCoords()->toArray();

        $exportData->solutions = array_map(function ($coord) {
            $solutionData = new \stdClass();
            $solutionData->id = (string) $coord->getId();
            $solutionData->value = $coord->getValue();
            $solutionData->shape = $coord->getShape();
            $solutionData->color = $coord->getColor();
            $solutionData->size = $coord->getSize();
            $solutionData->score = $coord->getScore();
            if ($coord->getFeedback()) {
                $solutionData->feedback = $coord->getFeedback();
            }

            return $solutionData;
        }, $coords);
        return $exportData;
    }

    /**
     * {@inheritdoc}
     */
    public function convertAnswerDetails(Response $response) {
        $parts = explode(';', $response->getResponse());

        return array_filter($parts, function ($part) {
            return $part !== '';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function validateAnswerFormat(Question $question, $data) {

        if (!is_array($data)) {
            return ['Answer data must be an array, ' . gettype($data) . ' given'];
        }
    }

    /**
     * @todo handle global score option
     *
     * {@inheritdoc}
     */
    public function storeAnswerAndMark(Question $question, Response $response, $data) {
        // a response is recorded like this : 471 - 335.9999694824219;583 - 125;
        $interaction = $this->om->getRepository('UJMExoBundle:InteractionGraphic')
                ->findOneByQuestion($question);
        $document = $interaction->getDocument();
        $coords = $interaction->getCoords();
        $score = 0;
        foreach ($coords as $coord) {
            $score += $coord->getScoreCoords();
        }
        if ($score === 0) {
            throw new \Exception('Global score not implemented yet');
        }



        //  471 - 335.9999694824219;583 - 125; <- format from UJM...Maybe choose another one
        // 471|335.9999694824219;583|125
        // array(
        //  "471-335.9999694824219",
        //  "583-125"
        // )
        //$answers = explode(';', $answer);
        $answers = array();
        foreach ($data as $answer) {
            if ($answer !== '') {
                $set = explode('-', $answer);
                $x = floatval($set[0]);
                $y = floatval($set[1]);
                array_push($answers, array("x" => $x, "y" => $y));
            }
        }
        $done = array();
        $mark = 0;
        foreach ($coords as $coord) {
            // no id in graphic responses
            if (in_array((string) $choice->getId(), $data)) {
                $mark += $choice->getWeight();
            }

            $values = $coord->getValue();

            $valueX = floatval($values[0]);
            $valueY = floatval($values[1]);
            $size = $coord->getSize(); // double
            // search into given answers for a correct one
            // original in Services->Interactions->Graphic->mark()
            foreach ($answers as $answer) {
                if (
                        ($answer['x'] <= ($valueX + $size)) // $answer['x'] + 8 < $xr + $valid... Why + 8 ?
                        && $answer['x'] >= $valueX // ($xa + 8) > ($xr)
                        && ($answer['y'] <= ($valueY + $size)) // + 8 ?
                        && $answer['y'] <= $valueY // + 8 ?
                        && !in_array($coord->getValue(), $done) // Avoid getting points twice for one answer
                    )
                    {
                    
                        $mark += $coord->getScoreCoords();
                        array_push($done,$coord->getValue());                   
                }
            }
        }

        if ($mark < 0) {
            $mark = 0;
        }
        // stroe answers like before x1-y1;x2-y2...
        $result = count($data) > 0 ? implode(';', $data) : '';

        $response->setResponse($result);
        $response->setMark($mark);
    }

}
