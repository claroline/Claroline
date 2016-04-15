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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * @DI\Service("ujm.exo.graphic_handler")
 * @DI\Tag("ujm.exo.question_handler")
 */
class GraphicHandler implements QuestionHandlerInterface {

    private $om;
    private $container;
    private $doctrine;

    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "container"       = @DI\Inject("service_container"),
     *     "doctrine"        = @DI\Inject("doctrine")
     * })
     *
     * @param ObjectManager $om
     * @param ContainerInterface $container
     * @param Registry $doctrine
     */
    public function __construct(ObjectManager $om, ContainerInterface $container, Registry $doctrine) {
        $this->om = $om;
        $this->container = $container;
        $this->doctrine = $doctrine;
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

        $picture = $this->om->getRepository('UJMExoBundle:Picture')->findOneBy(array('id' => $graphic->getPicture()));

        $exportData->width = $picture->getWidth();
        $exportData->height = $picture->getHeight();

        $document = new \stdClass();
        $document->id = $picture->getId();
        $document->label = $picture->getLabel();
        $document->url = $picture->getUrl();
        $exportData->document = $document;

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
                $solutionData->score = $coord->getScoreCoords();
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
            $solutionData->score = $coord->getScoreCoords();
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
        $coords = $interaction->getCoords();
        $score = 0;
        foreach ($coords as $coord) {
            $score += $coord->getScoreCoords();
        }
        if ($score === 0) {
            throw new \Exception('Global score not implemented yet');
        }

        $em = $this->doctrine->getManager();

        $rightCoords = $em->getRepository('UJMExoBundle:Coords')
            ->findBy(array('interactionGraphic' => $interaction->getId()));

        $serviceGraphic = $this->container->get("ujm.exo.graphic_service");

        $nbpointer = count($data);

        $responses = implode(',', $data);

        $coords2 = preg_split('[,]', $responses);

        $mark = $serviceGraphic->mark($responses, $nbpointer, $rightCoords, $coords2);

        if ($mark < 0) {
            $mark = 0;
        }
        // stroe answers like before x1-y1;x2-y2...
        $result = count($data) > 0 ? implode(';', $data) : '';

        $response->setResponse($result);
        $response->setMark($mark);
    }

}
