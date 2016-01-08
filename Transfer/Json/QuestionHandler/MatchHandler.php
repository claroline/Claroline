<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Proposal;
use UJM\ExoBundle\Entity\InteractionMatching;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerInterface;

/**
 * @DI\Service("ujm.exo.qcm_handler")
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

        // check solution ids are consistent with proposals ids
        $proposalsIds = array_map(function ($proposal) {
            return $proposal->id;
        }, $questionData->proposals);

        foreach ($questionData->solutions as $index => $solution) {
            if (!in_array($solution->id, $proposalsIds)) {
                $errors[] = [
                    'path' => "solutions[{$index}]",
                    'message' => "id {$solution->id} doesn't match any proposal id"
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

        for ($i = 0, $max = count($importData->proposals); $i < $max; ++$i) {
            // temporary limitation
            if ($importData->proposals[$i]->type !== 'text/plain') {
                throw new \Exception(
                "Import not implemented for MIME type {$importData->proposals[$i]->type}"
                );
            }

            $proposal = new Proposal();
            // label corresponding to proposal
            $proposal->setValue($importData->proposals[$i]->data);
            $proposal->setOrdre($i);

            foreach ($importData->solutions as $solution) {
                if ($solution->id === $importData->proposals[$i]->id) {
                    $proposal->setWeight($solution->score);

                    if (isset($solution->feedback)) {
                        $proposal->setFeedback($solution->feedback);
                    }
                }
            }

            $proposal->setInteractionMatching($interaction);
            $interaction->addProposal($proposal);
            $this->om->persist($proposal);
        }

        // to types : To bind / To drag
        // @todo check importData value(s) for this property
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
        // if needed shuffle choices
        if ($exportData->random && !$forPaperList) {
            $match->shuffleProposals();
            $match->shuffleLabels();
        } else {
            $match->sortProposals();
            $match->sortLabels();
        }

        $proposals = $match->getProposals()->toArray();
        $exportData->subType = $match->getTypeMatching()->getCode() === 1 ? 'toBind' : 'toDrag';
        $exportData->firstSet = array_map(function ($proposal) {
            $firstSetData = new \stdClass();
            $firstSetData->id = (string) $proposal->getId();
            $firstSetData->type = 'text/plain';
            $firstSetData->data = $proposal->getValue();
            return $firstSetData;
        }, $proposals);


        $exportData->secondSet = array();

        foreach ($proposals as $proposal) {
            $labels = $proposal->getAssociatedLabel();
            foreach ($labels as $label) {
                $secondSetData = new \stdClass();
                $secondSetData->id = (string) $label->getId();
                $secondSetData->type = 'text/plain';
                $secondSetData->data = $label->getValue();
                array_push($exportData->secondSet, $secondSetData);
            }
        }


        // 3 tables are used : 
        // - ujm_label (all info for a proposal including score_right_response)
        // - ujm_proposal_label (link between proposal and label ==> proposal_id | label_id)
        // - ujm_proposal (interaction_matching_id, value (== label), position_force, ordre)
        if ($withSolution) {
            $exportData->solutions = array_map(function ($proposal) {

                // getAssociatedLabel return an ArrayCollection !!!
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
        }

        return $exportData;
    }

    public function convertQuestionAnswers(Question $question, \stdClass $exportData) {
        $repo = $this->om->getRepository('UJMExoBundle:InteractionMatching');
        $match = $repo->findOneBy(['question' => $question]);

        $proposals = $match->getProposals()->toArray();
        $exportData->solutions = array_map(function ($proposal) {
            // getAssociatedLabel return an ArrayCollection !!!
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
        // for an answer we have : '1,2;2,1;' (to bindings proposals)
        // each part is composed with proposalId,linkedToId
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
            return ['Answer data cannot be empty'];
        }

        $interaction = $this->om->getRepository('UJMExoBundle:InteractionMatching')->findOneByQuestion($question);

        $proposals = $interaction->getProposals()->toArray();

        $proposalIds = array_map(function ($proposal) {
            return (string) $proposal->getId();
        }, $proposals);

        $labelsIds = array();
        foreach ($proposals as $proposal) {
            $labels = $proposal->getAssociatedLabel();
            foreach ($labels as $label) {

                array_push($labelsIds, (string) $label->getId());
            }
        }

        $answers = $data[0];
        $sourceIds = array();
        $targetIds = array();
        if ($answers !== '') {
            $idSets = explode(';', $answers);
            foreach ($idSets as $set) {
                $ids = explode(',', $set);
                array_push($sourceIds, $ids[0]);
                array_push($targetIds, $ids[1]);
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

        if ($count > 1) {
            return ['This question does not allow multiple answers'];
        }

        return [];
    }

    /**
     * @todo handle global score option
     * @todo check data format should be id1,id2 per proposal...
     *
     * {@inheritdoc}
     */
    public function storeAnswerAndMark(Question $question, Response $response, $data) {

        $interaction = $this->om->getRepository('UJMExoBundle:InteractionMatching')
                ->findOneByQuestion($question);

        $proposals = $interaction->getProposals();
        foreach ($proposals as $p) {
            // getAssociatedLabel return an ArrayCollection !!!
            $associatedLabels = $p->getAssociatedLabel();
            foreach ($associatedLabels as $label) {
                if (!$label->getScoreRightResponse()) {
                    throw new \Exception('Global score not implemented yet');
                }
            }
        }

        $mark = 0;

        foreach ($proposals as $p) {
            // getAssociatedLabel return an ArrayCollection !!!
            $associatedLabels = $p->getAssociatedLabel();
            foreach ($associatedLabels as $label) {
                // @todo check what to check for correct anwser (what is in $data ?) :: $p->getId(), $data is right... maybe need to split each data id1,id2
                if (in_array((string) $label->getId(), $data)) {
                    $mark += $label->getScoreRightResponse();
                }
            }
        }

        if ($mark < 0) {
            $mark = 0;
        }

        $response->setResponse(implode(';', $data) . ';');
        $response->setMark($mark);
    }

}
