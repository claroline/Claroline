<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Proposal;
use UJM\ExoBundle\Entity\Label;
use UJM\ExoBundle\Entity\InteractionMatching;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerInterface;

/**
 * @DI\Service("ujm.exo.match_handler")
 * @DI\Tag("ujm.exo.question_handler")
 */
class MatchHandler implements QuestionHandlerInterface {

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
        return 'application/x.match+json';
    }

    /**
     * {@inheritdoc}
     */
    public function getInteractionType() {
        return InteractionMatching::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonSchemaUri() {
        return 'http://json-quiz.github.io/json-quiz/schemas/question/match/schema.json';
    }

    /**
     * {@inheritdoc}
     */
    public function validateAfterSchema(\stdClass $questionData) {
        $errors = [];

        if (!isset($questionData->solutions)) {
            return $errors;
        }

        //print_r($questionData);die;
        // check solution ids are consistent with proposals ids
        $proposalsIds = array_map(function ($proposal) {
            return $proposal->id;
        }, $questionData->firstSet);

        $labelsIds = array_map(function ($label) {
            return $label->id;
        }, $questionData->secondSet);

        foreach ($questionData->solutions as $index => $solution) {
            if (!in_array($solution->firstId, $proposalsIds)) {
                $errors[] = [
                    'path' => "solutions[{$index}]",
                    'message' => "id {$solution->firstId} doesn't match any proposal id"
                ];
            }

            if (!in_array($solution->secondId, $labelsIds)) {
                $errors[] = [
                    'path' => "solutions[{$index}]",
                    'message' => "id {$solution->secondId} doesn't match any label id"
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
        $interaction = new InteractionMatching();

        // handle proposals
        for ($i = 0, $max = count($importData->firstSet); $i < $max; ++$i) {
            // temporary limitation
            if ($importData->firstSet[$i]->type !== 'text/plain') {
                throw new \Exception(
                "Import not implemented for MIME type {$importData->firstSet[$i]->type}"
                );
            }

            $proposal = new Proposal();
            $proposal->setValue($importData->firstSet[$i]->data);
            $proposal->setOrdre($i);
            $proposal->setInteractionMatching($interaction);
            $interaction->addProposal($proposal);
            $this->om->persist($proposal);
        }

        // handle labels
        for ($i = 0, $max = count($importData->secondSet); $i < $max; ++$i) {
            // temporary limitation
            if ($importData->secondSet[$i]->type !== 'text/plain') {
                throw new \Exception(
                "Import not implemented for MIME type {$importData->secondSet[$i]->type}"
                );
            }

            $label = new Label();
            $label->setValue($importData->secondSet[$i]->data);
            $label->setOrdre($i);

            foreach ($importData->solutions as $solution) {
                if ($solution->secondId === $importData->secondSet[$i]->id) {
                    $label->setScoreRightResponse($solution->score);

                    if (isset($solution->feedback)) {
                        $label->setFeedback($solution->feedback);
                    }
                }
            }

            $label->setInteractionMatching($interaction);
            $interaction->addLabel($label);
            $this->om->persist($label);
        }

        $subTypeCode = $importData->toBind ? 1 : 2;
        $subType = $this->om->getRepository('UJMExoBundle:TypeMatching')
                ->findOneByCode($subTypeCode);
        $interaction->setTypeMatching($subType);
        $interaction->setShuffle($importData->random);
        $interaction->setQuestion($question);
        $this->om->persist($interaction);
    }

    /**
     * {@inheritdoc}
     */
    public function convertInteractionDetails(Question $question, \stdClass $exportData, $withSolution = true, $forPaperList = false) {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionMatching');
        $match = $repo->findOneBy(['question' => $question]);
        $exportData->random = $match->getShuffle();
        // shuffle proposals and labels or sort them
        if ($exportData->random && !$forPaperList) {
            $match->shuffleProposals();
            $match->shuffleLabels();
        } else {
            $match->sortProposals();
            $match->sortLabels();
        }

        $proposals = $match->getProposals()->toArray();
        $exportData->toBind = $match->getTypeMatching()->getCode() === 1 ? true : false;
        $exportData->firstSet = array_map(function ($proposal) {
            $firstSetData = new \stdClass();
            $firstSetData->id = (string) $proposal->getId();
            $firstSetData->type = 'text/plain';
            $firstSetData->data = $proposal->getValue();
            return $firstSetData;
        }, $proposals);

        $labels = $match->getLabels()->toArray();
        $exportData->secondSet = array_map(function ($label) {
            $secondSetData = new \stdClass();
            $secondSetData->id = (string) $label->getId();
            $secondSetData->type = 'text/plain';
            $secondSetData->data = $label->getValue();
            return $secondSetData;
        }, $labels);

        if ($withSolution) {
            
            foreach($proposals as $proposal){
                // can be a proposal without label
                $associatedLabels = $proposal->getAssociatedLabel();
                if ($associatedLabels) {
                    $solutionData = new \stdClass();
                    $solutionData->firstId = (string) $proposal->getId();
                    foreach ($associatedLabels as $label) {
                        $solutionData->secondId = (string) $label->getId();
                        $solutionData->score = $label->getScoreRightResponse();
                        if ($label->getFeedback()) {
                            $solutionData->feedback = $label->getFeedback();
                        }
                    }
                    array_push($exportData->solutions, $solutionData);
                }
            }

            /*$exportData->solutions = array_map(function ($proposal) {
                // can be a proposal without label
                $associatedLabels = $proposal->getAssociatedLabel();
                if ($associatedLabels) {
                    $solutionData = new \stdClass();
                    $solutionData->firstId = (string) $proposal->getId();
                    foreach ($associatedLabels as $label) {
                        $solutionData->secondId = (string) $label->getId();
                        $solutionData->score = $label->getScoreRightResponse();
                        if ($label->getFeedback()) {
                            $solutionData->feedback = $label->getFeedback();
                        }
                    }
                    return $solutionData;
                }
            }, $proposals);*/
        }

        return $exportData;
    }

    public function convertQuestionAnswers(Question $question, \stdClass $exportData) {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionMatching');
        $match = $repo->findOneBy(['question' => $question]);

        $proposals = $match->getProposals()->toArray();
        $exportData->solutions = array_map(function ($proposal) {
            $associatedLabels = $proposal->getAssociatedLabel();
            foreach ($associatedLabels as $label) {
                $solutionData = new \stdClass();
                $solutionData->firstId = (string) $proposal->getId();
                $solutionData->secondId = (string) $label->getId();
                $solutionData->score = $label->getScoreRightResponse();
                if ($label->getFeedback()) {
                    $solutionData->feedback = $label->getFeedback();
                }
            }

            return $solutionData;
        }, $proposals);
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

        $count = 0;
        if (0 === $count = count($data)) {
            // no need to check anything
            return [];
        }

        $interaction = $this->om->getRepository('UJMExoBundle:InteractionMatching')->findOneByQuestion($question);

        $proposals = $interaction->getProposals()->toArray();

        $proposalIds = array_map(function ($proposal) {
            return (string) $proposal->getId();
        }, $proposals);

        $labels = $interaction->getLabels()->toArray();
        $labelsIds = array_map(function ($label) {
            return (string) $label->getId();
        }, $labels);

        $sourceIds = array();
        $targetIds = array();
        foreach ($data as $answer) {
            if ($answer !== '') {
                $set = explode(',', $answer);
                array_push($sourceIds, $set[0]);
                array_push($targetIds, $set[1]);
            }
        }

        foreach ($sourceIds as $id) {
            if (!is_string($id)) {
                return ['Answer array must contain only string identifiers'];
            }

            if (!in_array($id, $proposalIds)) {
                return ['Answer array identifiers must reference a question proposal id'];
            }
        }

        foreach ($targetIds as $id) {
            if (!is_string($id)) {
                return ['Answer array must contain only string identifiers'];
            }

            if (!in_array($id, $labelsIds)) {
                return ['Answer array identifiers must reference a question proposal associated label id'];
            }
        }
        return [];
    }

    /**
     * @todo handle global score option
     *
     * {@inheritdoc}
     */
    public function storeAnswerAndMark(Question $question, Response $response, $data) {

        $interaction = $this->om->getRepository('UJMExoBundle:InteractionMatching')
                ->findOneByQuestion($question);

        $labels = $interaction->getLabels();
        // at least one label must have a score
        $score = 0;
        foreach ($labels as $label) {
            $score += $label->getScoreRightResponse();
        }
        if ($score === 0) {
            throw new \Exception('Global score not implemented yet');
        }

        // calculate response score
        $mark = 0;
        $targetIds = array();
        foreach ($data as $answer) {
            if ($answer !== '') {
                $set = explode(',', $answer);
                array_push($targetIds, $set[1]);
            }
        }

        foreach ($labels as $label) {
            // if student used the label in his answer
            if (in_array((string) $label->getId(), $targetIds)) {
                $mark += $label->getScoreRightResponse();
            }
        }

        if ($mark < 0) {
            $mark = 0;
        }

        $result = count($data) > 0 ? implode(';', $data) : '';
        $response->setResponse($result);
        $response->setMark($mark);
    }

}
