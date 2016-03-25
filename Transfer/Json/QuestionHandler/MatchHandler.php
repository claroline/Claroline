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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("ujm.exo.match_handler")
 * @DI\Tag("ujm.exo.question_handler")
 */
class MatchHandler implements QuestionHandlerInterface {

    private $om;
    private $container;
    
    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "container"       = @DI\Inject("service_container")
     * })
     * 
     * @param ObjectManager $om
     * @param ContainerInterface $container
     */
    public function __construct(ObjectManager $om, ContainerInterface $container) {
        $this->om = $om;
        $this->container = $container;
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
        // used by QuestionManager->importQuestion
        // this importQuestion method seems to be used only in test context...
        $interaction = new InteractionMatching();

        // handle proposals
        $persistedProposals = array(); // do not add twice the same label!!
        // for each firstSet in data (= proposal)
        for ($i = 0, $max = count($importData->firstSet); $i < $max; ++$i) {
            // temporary limitation
            if ($importData->firstSet[$i]->type !== 'text/plain') {
                throw new \Exception(
                "Import not implemented for MIME type {$importData->firstSet[$i]->type}"
                );
            }
            // create a Proposal
            $proposal = new Proposal();
            $proposal->setValue($importData->firstSet[$i]->data);
            $proposal->setOrdre($i);
            $proposal->setInteractionMatching($interaction);
            $interaction->addProposal($proposal);
            $this->om->persist($proposal);
            array_push($persistedProposals, $proposal);
        }

        for ($j = 0, $max = count($importData->secondSet); $j < $max; ++$j) {
            if ($importData->secondSet[$j]->type !== 'text/plain') {
                throw new \Exception(
                "Import not implemented for MIME type {$importData->secondSet[$j]->type}"
                );
            }
            // create interraction label in any case
            $label = new Label();
            $label->setValue($importData->secondSet[$j]->data);
            $label->setOrdre($j);
            // check if current label is in the solution
            foreach ($importData->solutions as $solution) {
                // label is in solution get score from solution
                if ($solution->secondId === $importData->secondSet[$j]->id) {
                    $label->setScoreRightResponse($solution->score);

                    // here we should add proposal to label $proposal->addAssociatedLabel($label);
                    // but how to retrieve the correct proposal (no flush = no id) ??
                    // find this solution a bit overkill
                    // @TODO find a better way to do that!!!!
                    for ($k = 0, $max = count($importData->firstSet); $k < $max; ++$k) {
                        if ($solution->firstId === $importData->firstSet[$k]->id) {
                            $value = $importData->firstSet[$k]->data;
                            for ($l = 0, $max = count($persistedProposals); $l < $max; ++$l) {
                                // find proposal by label... Unicity is not ensured!!!!
                                if ($persistedProposals[$l]->getValue() == $value) {
                                    $persistedProposals[$l]->addAssociatedLabel($label);
                                    $this->om->persist($persistedProposals[$l]);
                                    break;
                                }
                            }
                        }
                    }
                }

                // get feedback from solution
                if (isset($solution->feedback)) {
                    $label->setFeedback($solution->feedback);
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

            $exportData->solutions = array_map(function ($proposal) {
                $associatedLabels = $proposal->getAssociatedLabel();
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
            }, $proposals);
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
        $tabLabelGraduate = array(); // store labels already considered in calculating the score
        foreach ($labels as $label) {
             // if first label
             if(count($tabLabelGraduate) === 0){
               $score += $label->getScoreRightResponse();
             } else if (count($tabLabelGraduate) > 0){
               foreach($tabLabelGraduate as $labelPast) { // nothing in the array
                   if ($labelPast !== $label) {
                       $score += $label->getScoreRightResponse();
                   }
               }
             }

             // add the labels already considered
             array_push($tabLabelGraduate, $label);
         }
        if ($score === 0) {
            throw new \Exception('Global score not implemented yet');
        }
        
        $serviceMatching = $this->container->get("ujm.exo.matching_service");
        
        $tabsResponses = $serviceMatching->initTabResponseMatching($data, $interaction);
        $tabRightResponse = $tabsResponses[1];
        $tabResponseIndex = $tabsResponses[0];

        $mark = $serviceMatching->mark($interaction, 0, $tabRightResponse, $tabResponseIndex);

        if ($mark < 0) {
            $mark = 0;
        }

        $result = count($data) > 0 ? implode(';', $data) : '';
        $response->setResponse($result);
        $response->setMark($mark);
     }

}
