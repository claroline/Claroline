<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\Misc\Association;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Content\ContentSerializer;

class SetQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var ContentSerializer
     */
    private $contentSerializer;

    /**
     * SetQuestionSerializer constructor.
     */
    public function __construct(ContentSerializer $contentSerializer)
    {
        $this->contentSerializer = $contentSerializer;
    }

    public function getName()
    {
        return 'exo_question_set';
    }

    /**
     * Converts a Set question into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(MatchQuestion $setQuestion, array $options = [])
    {
        $serialized = [
            'random' => $setQuestion->getShuffle(),
            'penalty' => $setQuestion->getPenalty(),
        ];

        $items = array_map(function (Proposal $proposal) use ($options) {
            $setData = $this->contentSerializer->serialize($proposal, $options);
            $setData['id'] = $proposal->getUuid();

            return $setData;
        }, $setQuestion->getProposals()->toArray());

        $sets = array_map(function (Label $label) use ($options) {
            $itemData = $this->contentSerializer->serialize($label, $options);
            $itemData['id'] = $label->getUuid();

            return $itemData;
        }, $setQuestion->getLabels()->toArray());

        if ($setQuestion->getShuffle() && in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
            shuffle($sets);
            shuffle($items);
        }

        $serialized['sets'] = $sets;
        $serialized['items'] = $items;

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($setQuestion);
        }

        return $serialized;
    }

    private function serializeSolutions(MatchQuestion $setQuestion)
    {
        $solutions = [
            'associations' => [],
            'odd' => [],
        ];

        foreach ($setQuestion->getAssociations() as $association) {
            $solutionData = ['itemId' => $association->getProposal()->getUuid()];
            $isOdd = false;

            if ($association->getLabel()) {
                $solutionData['setId'] = $association->getLabel()->getUuid();
            } else {
                $isOdd = true;
            }

            $solutionData['score'] = $association->getScore();

            if ($association->getFeedback()) {
                $solutionData['feedback'] = $association->getFeedback();
            }

            $isOdd ? $solutions['odd'][] = $solutionData : $solutions['associations'][] = $solutionData;
        }

        return $solutions;
    }

    /**
     * Converts raw data into a Set question entity.
     *
     * @param array         $data
     * @param MatchQuestion $setQuestion
     *
     * @return MatchQuestion
     */
    public function deserialize($data, MatchQuestion $setQuestion = null, array $options = [])
    {
        if (empty($setQuestion)) {
            $setQuestion = new MatchQuestion();
        }

        if (!empty($data['penalty']) || 0 === $data['penalty']) {
            $setQuestion->setPenalty($data['penalty']);
        }

        $this->sipe('random', 'setShuffle', $data, $setQuestion);

        // deserialize proposals labels and solutions
        $this->deserializeLabels($setQuestion, $data['sets']);
        $this->deserializeProposals($setQuestion, $data['items']);
        $this->deserializeSolutions($setQuestion, array_merge($data['solutions']['associations'], $data['solutions']['odd']));

        return $setQuestion;
    }

    /**
     * Deserializes Question labels.
     *
     * @param array $sets ie labels
     */
    private function deserializeLabels(MatchQuestion $setQuestion, array $sets)
    {
        $labelsEntities = $setQuestion->getLabels()->toArray();

        foreach ($sets as $index => $setData) {
            $label = null;
            // Searches for an existing Label entity.
            foreach ($labelsEntities as $entityIndex => $entityLabel) {
                /** @var Label $entityLabel */
                if ($entityLabel->getUuid() === $setData['id']) {
                    $label = $entityLabel;
                    unset($labelsEntities[$entityIndex]);
                    break;
                }
            }

            $label = $label ?: new Label();
            $label->setUuid($setData['id']);

            $label->setOrder($index);

            // Deserialize firstSet content
            $label = $this->contentSerializer->deserialize($setData, $label);
            $setQuestion->addLabel($label);
        }

        // Remaining labels are no longer in the Question
        foreach ($labelsEntities as $labelToRemove) {
            $setQuestion->removeLabel($labelToRemove);
        }
    }

    /**
     * Deserializes Question proposals.
     *
     * @param array $items ie proposals
     */
    private function deserializeProposals(MatchQuestion $setQuestion, array $items)
    {
        $proposalsEntities = $setQuestion->getProposals()->toArray();

        foreach ($items as $index => $itemData) {
            $proposal = null;

            // Search for an existing Proposal entity.
            foreach ($proposalsEntities as $entityIndex => $entityProposal) {
                /* @var Proposal $entityProposal */
                if ($entityProposal->getUuid() === $itemData['id']) {
                    $proposal = $entityProposal;

                    unset($proposalsEntities[$entityIndex]);
                    break;
                }
            }

            $proposal = $proposal ?: new Proposal();
            $proposal->setUuid($itemData['id']);
            $proposal->setOrder($index);

            // Deserialize proposal content
            $proposal = $this->contentSerializer->deserialize($itemData, $proposal);
            $setQuestion->addProposal($proposal);
        }

        // Remaining proposals are no longer in the Question
        foreach ($proposalsEntities as $proposalToRemove) {
            $setQuestion->removeProposal($proposalToRemove);
        }
    }

    /**
     * Deserializes Question solutions.
     */
    private function deserializeSolutions(MatchQuestion $setQuestion, array $solutionsAndOdd)
    {
        $associationsEntities = $setQuestion->getAssociations()->toArray();

        foreach ($solutionsAndOdd as $solution) {
            $association = null;

            // Search for an existing Proposal entity.
            foreach ($associationsEntities as $entityIndex => $entityAssociation) {
                /* @var Association $entityAssociation */
                // retieves oddAssociations and fullAssociation
                if ($entityAssociation->getProposal()->getUuid() === $solution['itemId'] &&
                      (
                        ($entityAssociation->getLabel() && $entityAssociation->getLabel()->getUuid() === $solution['setId']) ||
                        (!$entityAssociation->getLabel() && !isset($solution['setId']))
                      )
                ) {
                    $association = $entityAssociation;

                    unset($associationsEntities[$entityIndex]);
                    break;
                }
            }

            if (null === $association) {
                // Create a new Association
                $association = new Association();
                // add association label
                foreach ($setQuestion->getProposals() as $proposal) {
                    if ($proposal->getUuid() === $solution['itemId']) {
                        $association->setProposal($proposal);
                        break;
                    }
                }

                // add association proposal if any
                if (isset($solution['setId'])) {
                    foreach ($setQuestion->getLabels() as $label) {
                        if ($label->getUuid() === $solution['setId']) {
                            $association->setLabel($label);
                            break;
                        }
                    }
                }
            }

            $association->setScore($solution['score']);

            if (isset($solution['feedback'])) {
                $association->setFeedback($solution['feedback']);
            }
            $setQuestion->addAssociation($association);
        }

        // Remaining associations are no longer in the Question
        foreach ($associationsEntities as $associationToRemove) {
            $setQuestion->removeAssociation($associationToRemove);
        }
    }
}
