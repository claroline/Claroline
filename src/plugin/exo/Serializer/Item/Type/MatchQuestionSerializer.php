<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\Misc\Association;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Content\ContentSerializer;

class MatchQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var ContentSerializer
     */
    private $contentSerializer;

    /**
     * MatchQuestionSerializer constructor.
     */
    public function __construct(ContentSerializer $contentSerializer)
    {
        $this->contentSerializer = $contentSerializer;
    }

    public function getName()
    {
        return 'exo_question_match';
    }

    /**
     * Converts a Match question into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(MatchQuestion $matchQuestion, array $options = [])
    {
        $serialized = [
            'random' => $matchQuestion->getShuffle(),
            'penalty' => $matchQuestion->getPenalty(),
        ];

        $firstSet = array_map(function (Proposal $proposal) use ($options) {
            $itemData = $this->contentSerializer->serialize($proposal, $options);
            $itemData['id'] = $proposal->getUuid();

            return $itemData;
        }, $matchQuestion->getProposals()->toArray());

        $secondSet = array_map(function (Label $label) use ($options) {
            $itemData = $this->contentSerializer->serialize($label, $options);
            $itemData['id'] = $label->getUuid();

            return $itemData;
        }, $matchQuestion->getLabels()->toArray());

        if ($matchQuestion->getShuffle() && in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
            shuffle($firstSet);
            shuffle($secondSet);
        }

        $serialized['firstSet'] = $firstSet;
        $serialized['secondSet'] = $secondSet;

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($matchQuestion);
        }

        return $serialized;
    }

    private function serializeSolutions(MatchQuestion $matchQuestion)
    {
        $solutions = [];

        foreach ($matchQuestion->getAssociations() as $association) {
            $solutionData = [
                'firstId' => $association->getProposal()->getUuid(),
                'secondId' => $association->getLabel()->getUuid(),
                'score' => $association->getScore(),
            ];

            if ($association->getFeedback()) {
                $solutionData['feedback'] = $association->getFeedback();
            }

            $solutions[] = $solutionData;
        }

        return $solutions;
    }

    /**
     * Converts raw data into a Match question entity.
     *
     * @param array         $data
     * @param MatchQuestion $matchQuestion
     *
     * @return MatchQuestion
     */
    public function deserialize($data, MatchQuestion $matchQuestion = null, array $options = [])
    {
        if (empty($matchQuestion)) {
            $matchQuestion = new MatchQuestion();
        }

        if (isset($data['penaty'])) {
            if (!empty($data['penalty']) || 0 === $data['penalty']) {
                $matchQuestion->setPenalty($data['penalty']);
            }
        }
        $this->sipe('random', 'setShuffle', $data, $matchQuestion);

        $this->deserializeLabels($matchQuestion, $data['secondSet'], $options);
        $this->deserializeProposals($matchQuestion, $data['firstSet'], $options);
        $this->deserializeSolutions($matchQuestion, $data['solutions']);

        return $matchQuestion;
    }

    /**
     * Deserializes Question labels.
     *
     * @param array $secondSets ie labels
     */
    private function deserializeLabels(MatchQuestion $matchQuestion, array $secondSets, array $options = [])
    {
        $secondSetEntities = $matchQuestion->getLabels()->toArray();

        foreach ($secondSets as $index => $secondSetData) {
            $label = null;
            // Searches for an existing Label entity.
            foreach ($secondSetEntities as $entityIndex => $entityLabel) {
                /** @var Label $entityLabel */
                if ($entityLabel->getUuid() === $secondSetData['id']) {
                    $label = $entityLabel;
                    unset($secondSetEntities[$entityIndex]);
                    break;
                }
            }

            $label = $label ?: new Label();
            $label->setUuid($secondSetData['id']);
            $label->setOrder($index);

            // Deserialize firstSet content
            $label = $this->contentSerializer->deserialize($secondSetData, $label, $options);
            $matchQuestion->addLabel($label);
        }

        // Remaining labels are no longer in the Question
        foreach ($secondSetEntities as $labelToRemove) {
            $matchQuestion->removeLabel($labelToRemove);
        }
    }

    /**
     * Deserializes Question proposals.
     *
     * @param array $firstSets ie proposals
     */
    private function deserializeProposals(MatchQuestion $matchQuestion, array $firstSets, array $options = [])
    {
        $firstSetEntities = $matchQuestion->getProposals()->toArray();

        foreach ($firstSets as $index => $firstSetData) {
            $proposal = null;

            // Search for an existing Proposal entity.
            foreach ($firstSetEntities as $entityIndex => $entityProposal) {
                /* @var Label $entityProposal */
                if ($entityProposal->getUuid() === $firstSetData['id']) {
                    $proposal = $entityProposal;

                    unset($firstSetEntities[$entityIndex]);
                    break;
                }
            }

            $proposal = $proposal ?: new Proposal();
            $proposal->setUuid($firstSetData['id']);
            $proposal->setOrder($index);

            // Deserialize proposal content
            $proposal = $this->contentSerializer->deserialize($firstSetData, $proposal, $options);
            $matchQuestion->addProposal($proposal);
        }

        // Remaining proposals are no longer in the Question
        foreach ($firstSetEntities as $proposalToRemove) {
            $matchQuestion->removeProposal($proposalToRemove);
        }
    }

    /**
     * Deserializes Question solutions.
     */
    private function deserializeSolutions(MatchQuestion $matchQuestion, array $solutions)
    {
        $associationsEntities = $matchQuestion->getAssociations()->toArray();

        foreach ($solutions as $solution) {
            $association = null;

            // Search for an existing Proposal entity.
            foreach ($associationsEntities as $entityIndex => $entityAssociation) {
                /* @var Association $entityAssociation */
                if ($entityAssociation->getProposal()->getUuid() === $solution['firstId'] && $entityAssociation->getLabel()->getUuid() === $solution['secondId']) {
                    $association = $entityAssociation;

                    unset($associationsEntities[$entityIndex]);
                    break;
                }
            }

            if (null === $association) {
                // Create a new Association
                $association = new Association();
                // add association label
                foreach ($matchQuestion->getLabels() as $label) {
                    if ($label->getUuid() === $solution['secondId']) {
                        $association->setLabel($label);
                        break;
                    }
                }
                // add association proposal
                foreach ($matchQuestion->getProposals() as $proposal) {
                    if ($proposal->getUuid() === $solution['firstId']) {
                        $association->setProposal($proposal);
                        break;
                    }
                }
            }

            $association->setScore($solution['score']);

            if (isset($solution['feedback'])) {
                $association->setFeedback($solution['feedback']);
            }
            $matchQuestion->addAssociation($association);
        }

        // Remaining associations are no longer in the Question
        foreach ($associationsEntities as $associationToRemove) {
            $matchQuestion->removeAssociation($associationToRemove);
        }
    }
}
