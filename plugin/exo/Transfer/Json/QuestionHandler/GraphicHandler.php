<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\InteractionGraphic;
use UJM\ExoBundle\Entity\Picture;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Services\classes\Interactions\Graphic;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerInterface;

/**
 * @DI\Service("ujm.exo.graphic_handler")
 * @DI\Tag("ujm.exo.question_handler")
 */
class GraphicHandler implements QuestionHandlerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Graphic
     */
    private $graphicService;

    /**
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "graphicService" = @DI\Inject("ujm.exo.graphic_service")
     * })
     *
     * @param ObjectManager $om
     * @param Graphic       $graphicService
     */
    public function __construct(ObjectManager $om, Graphic $graphicService)
    {
        $this->om = $om;
        $this->graphicService = $graphicService;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionMimeType()
    {
        return 'application/x.graphic+json';
    }

    /**
     * {@inheritdoc}
     */
    public function getInteractionType()
    {
        return InteractionGraphic::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonSchemaUri()
    {
        return 'http://json-quiz.github.io/json-quiz/schemas/question/graphic/schema.json';
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

        // check solution ids are consistent with coords ids
        $coordIds = array_map(function ($coord) {
            return $coord->id;
        }, $questionData->coords);

        foreach ($questionData->solutions as $index => $solution) {
            if (!in_array($solution->id, $coordIds)) {
                $errors[] = [
                    'path' => "solutions[{$index}]",
                    'message' => "id {$solution->id} doesn't match any coord id",
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
                'message' => 'there is no solution with a positive score',
            ];
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function persistInteractionDetails(Question $question, \stdClass $importData)
    {
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

        // should we upload the picture ??
        $picture = new Picture();
        $picture->setLabel($importData->document->label ? $importData->document->label : '');
        $picture->setUrl($importData->document->url);
        $picture->setWidth($importData->width);
        $picture->setHeight($importData->height);

        $ext = pathinfo($importData->document->url)['extension'];
        $picture->setType($ext);

        $this->om->persist($picture);

        $interaction->setPicture($picture);
        $interaction->setQuestion($question);

        $this->om->persist($interaction);
    }

    /**
     * {@inheritdoc}
     */
    public function convertInteractionDetails(Question $question, \stdClass $exportData, $withSolution = true, $forPaperList = false)
    {
        $interaction = $this->om->getRepository('UJMExoBundle:InteractionGraphic')->findOneBy([
            'question' => $question,
        ]);

        $exportData->image = $this->exportImage($interaction->getPicture());
        $exportData->pointers = $interaction->getCoords()->count();
        $exportData->scoreTotal = $this->graphicService->maxScore($interaction);

        if ($withSolution) {
            $exportData->solutions = $this->exportSolutions($interaction->getCoords()->toArray());
        }

        return $exportData;
    }

    /**
     * Export question solutions.
     *
     * @param array $solutions
     *
     * @return \stdClass
     */
    private function exportSolutions(array $solutions)
    {
        return array_map(function (Coords $coords) {
            $solutionData = new \stdClass();
            $solutionData->area = $this->exportArea($coords);
            $solutionData->score = $coords->getScoreCoords();
            if ($coords->getFeedback()) {
                $solutionData->feedback = $coords->getFeedback();
            }

            return $solutionData;
        }, $solutions);
    }

    /**
     * Export question image.
     *
     * @param Picture $picture
     *
     * @return \stdClass
     */
    private function exportImage(Picture $picture)
    {
        // Export Image
        $image = new \stdClass();

        $image->id = $picture->getId();
        $image->url = $picture->getUrl();
        $image->label = $picture->getLabel();
        $image->width = $picture->getWidth();
        $image->height = $picture->getHeight();

        return $image;
    }

    /**
     * Export question areas.
     *
     * @param Coords $coords
     *
     * @return \stdClass
     */
    private function exportArea(Coords $coords)
    {
        $exportData = new \stdClass();
        $exportData->id = $coords->getId();
        $exportData->color = $coords->getColor();

        $position = explode(',', $coords->getValue());

        switch ($coords->getShape()) {
            case 'circle':
                $exportData->shape = 'circle';

                $exportData->radius = $coords->getSize() / 2;

                // We store the top left corner, so we need to calculate the real center
                $center = $this->exportCoords($position);
                $center->x += $exportData->radius;
                $center->y += $exportData->radius;
                $exportData->center = $center;

                break;
            case 'square':
                $exportData->shape = 'rect';
                $exportData->coords = [
                    // top-left coords
                    $this->exportCoords($position),
                    // bottom-right coords
                    $this->exportCoords([$position[0] + $coords->getSize(), $position[1] + $coords->getSize()]),
                ];

                break;
        }

        return $exportData;
    }

    /**
     * @param array $position
     *
     * @return \stdClass
     */
    private function exportCoords(array $position)
    {
        $exportData = new \stdClass();

        $exportData->x = (int) $position[0];
        $exportData->y = (int) $position[1];

        return $exportData;
    }

    public function convertQuestionAnswers(Question $question, \stdClass $exportData)
    {
        $interaction = $this->om->getRepository('UJMExoBundle:InteractionGraphic')->findOneBy([
            'question' => $question,
        ]);

        $exportData->solutions = $this->exportSolutions($interaction->getCoords()->toArray());

        return $exportData;
    }

    /**
     * {@inheritdoc}
     */
    public function convertAnswerDetails(Response $response)
    {
        $parts = explode(';', $response->getResponse());

        $answers = [];
        foreach ($parts as $coords) {
            if ('' !== $coords) {
                $answers[] = $this->exportCoords(explode('-', $coords));
            }
        }

        return $answers;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAnswerFormat(Question $question, $data)
    {
        if (!is_array($data)) {
            return ['Answer data must be an array, '.gettype($data).' given'];
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
        // a response is recorded like this : 471 - 335.9999694824219;583 - 125;
        $interaction = $this->om->getRepository('UJMExoBundle:InteractionGraphic')
            ->findOneByQuestion($question);
        $coords = $interaction->getCoords();
        $score = 0;
        foreach ($coords as $coord) {
            $score += $coord->getScoreCoords();
        }
        if ($score === 0) {
            throw new \Exception('Global score not implemented yet');
        }

        $rightCoords = $this->om->getRepository('UJMExoBundle:Coords')
            ->findBy(array('interactionGraphic' => $interaction->getId()));

        $answer = implode(';', array_map(function ($coords) {
            return (string) $coords['x'].'-'.(string) $coords['y'];
        }, $data));

        // TODO : it would be easier to mark if we pass directly the decoded array of coords instead of the encode string
        $mark = $this->graphicService->mark($answer, $rightCoords, 0);
        if ($mark < 0) {
            $mark = 0;
        }

        $response->setResponse($answer);
        $response->setMark($mark);
    }
}
