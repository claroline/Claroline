<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerInterface;

/**
 * @DI\Service("ujm.exo.qcm_handler")
 * @DI\Tag("ujm.exo.question_handler")
 */
class QcmHandler implements QuestionHandlerInterface
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
        return 'application/x.choice+json';
    }

    /**
     * {@inheritdoc}
     */
    public function getInteractionType()
    {
        return InteractionQCM::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonSchemaUri()
    {
        return 'http://json-quiz.github.io/json-quiz/schemas/question/choice/schema.json';
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
        $choiceIds = array_map(function ($choice) {
            return $choice->id;
        }, $questionData->choices);

        foreach ($questionData->solutions as $index => $solution) {
            if (!in_array($solution->id, $choiceIds)) {
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
        $interaction = new InteractionQCM();

        for ($i = 0, $max = count($importData->choices); $i < $max; ++$i) {
            // temporary limitation
            if ($importData->choices[$i]->type !== 'text/html') {
                throw new \Exception(
                    "Import not implemented for MIME type {$importData->choices[$i]->type}"
                );
            }

            $choice = new Choice();
            $choice->setLabel($importData->choices[$i]->data);
            $choice->setOrdre($i);

            foreach ($importData->solutions as $solution) {
                if ($solution->id === $importData->choices[$i]->id) {
                    $choice->setWeight($solution->score);

                    if (isset($solution->feedback)) {
                        $choice->setFeedback($solution->feedback);
                    }
                }
            }

            $choice->setInteractionQCM($interaction);
            $interaction->addChoice($choice);
            $this->om->persist($choice);
        }

        $subTypeCode = $importData->multiple ? 1 : 2;
        $subType = $this->om->getRepository('UJMExoBundle:TypeQCM')
            ->findOneByCode($subTypeCode);
        $interaction->setTypeQCM($subType);
        $interaction->setShuffle($importData->random);
        $interaction->setQuestion($question);
        $this->om->persist($interaction);
    }

    /**
     * {@inheritdoc}
     */
    public function convertInteractionDetails(Question $question, \stdClass $exportData, $withSolution = true, $forPaperList = false)
    {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionQCM');
        $qcm = $repo->findOneBy(['question' => $question]);
        $exportData->random = $qcm->getShuffle();
        // if needed shuffle choices
        if($exportData->random && !$forPaperList){
            $qcm->shuffleChoices();
        }
        
        $choices = $qcm->getChoices()->toArray();

        $exportData->multiple = $qcm->getTypeQCM()->getCode() === 1;    
        $exportData->choices = array_map(function ($choice) {
            $choiceData = new \stdClass();
            $choiceData->id = (string) $choice->getId();
            $choiceData->type = 'text/html';
            $choiceData->data = $choice->getLabel();

            return $choiceData;
        }, $choices);

        if ($withSolution) {
            $exportData->solutions = array_map(function ($choice) {
                $solutionData = new \stdClass();
                $solutionData->id = (string) $choice->getId();
                $solutionData->score = $choice->getWeight();

                if ($choice->getFeedback()) {
                    $solutionData->feedback = $choice->getFeedback();
                }

                return $solutionData;
            }, $choices);
        }

        return $exportData;
    }
    
    public function convertQuestionAnswers(Question $question, \stdClass $exportData){
        $repo = $this->om->getRepository('UJMExoBundle:InteractionQCM');
        $qcm = $repo->findOneBy(['question' => $question]);
        
        $choices = $qcm->getChoices()->toArray();
        $exportData->solutions = array_map(function ($choice) {
                $solutionData = new \stdClass();
                $solutionData->id = (string) $choice->getId();
                $solutionData->score = $choice->getWeight();

                if ($choice->getFeedback()) {
                    $solutionData->feedback = $choice->getFeedback();
                }

                return $solutionData;
            }, $choices);
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
            //return ['Answer data cannot be empty'];
            // data CAN be empty ! (for example editing a multiple choice question and unchecking all choices)
            return [];
        }

        $interaction = $this->om->getRepository('UJMExoBundle:InteractionQCM')
            ->findOneByQuestion($question);
        $choiceIds = array_map(function ($choice) {
            return (string) $choice->getId();
        }, $interaction->getChoices()->toArray());

        foreach ($data as $id) {
            if (!is_string($id)) {
                return ['Answer array must contain only string identifiers'];
            }

            if (!in_array($id, $choiceIds)) {
                return ['Answer array identifiers must reference question choices'];
            }
        }
        
        if ($interaction->getTypeQCM()->getCode() === 2 && $count > 1) {
            return ['This question does not allow multiple answers'];
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
        $interaction = $this->om->getRepository('UJMExoBundle:InteractionQCM')
            ->findOneByQuestion($question);

        if (!$interaction->getWeightResponse()) {
            throw new \Exception('Global score not implemented yet');
        }

        $mark = 0;

        foreach ($interaction->getChoices() as $choice) {
            if (in_array((string) $choice->getId(), $data)) {
                $mark += $choice->getWeight();
            }
        }

        if ($mark < 0) {
            $mark = 0;
        }
        
        $result = count($data) > 0 ? implode(';', $data) . ';' : '';

        $response->setResponse($result);
        $response->setMark($mark);
    }
}
