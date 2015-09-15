<?php

/**
 * To import a Matching question in QTI.
 */
namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Proposal;
use UJM\ExoBundle\Entity\Label;
use UJM\ExoBundle\Entity\InteractionMatching;

class MatchingImport extends QtiImport
{
    protected $interactionMatching;
    protected $associatedLabels = [];

    /**
     * Implements the abstract method.
     *
     *
     * @param qtiRepository $qtiRepos
     * @param DOMElement    $assessmentItem assessmentItem of the question to imported
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem)
    {
        $this->qtiRepos = $qtiRepos;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);
        $this->createQuestion('UJM\ExoBundle\Entity\InteractionMatching');
        $this->createInteractionMatching();

        return $this->interactionMatching;
    }

    /**
     * Implements the abstract method.
     *
     *
     * @return $text
     */
    protected function getPrompt()
    {
        $prompt = '';
        $ib = $this->assessmentItem->getElementsByTagName('itemBody')->item(0);
        $ci = $ib->getElementsByTagName('matchInteraction')->item(0);
        $text = '';
        if ($ci->getElementsByTagName('prompt')->item(0)) {
            $prompt = $ci->getElementsByTagName('prompt')->item(0);
            $text = $this->domElementToString($prompt);
            $text = str_replace('<prompt>', '', $text);
            $text = str_replace('</prompt>', '', $text);
            $text = html_entity_decode($text);
        }

        return $text;
    }

    /**
     * Create the InteractionMatching object.
     */
    protected function createInteractionMatching()
    {
        $this->interactionMatching = new InteractionMatching();
        $this->interactionMatching->setQuestion($this->question);
        //for recording the type of the question
        $this->matchingType();
        $this->getShuffle();
        $this->om->persist($this->interactionMatching);
        $this->om->flush();
        $this->createLabels();
        $this->createProposals();
    }

    /**
     * Get shuffle.
     */
    protected function getShuffle()
    {
        $ib = $this->assessmentItem->getElementsByTagName('itemBody')->item(0);
        $mi = $ib->getElementsBYTagName('matchInteraction')->item(0);
        $shuffle = $mi->getAttribute('shuffle');
        if ($shuffle == 'true') {
            $this->interactionMatching->setShuffle(true);
        } else {
            $this->interactionMatching->setShuffle(false);
        }
        $this->om->persist($this->interactionMatching);
        $this->om->flush();
    }

    /**
     * Create Labels in BDD.
     */
    protected function createLabels()
    {
        $ordre = 0;
        $ib = $this->assessmentItem->getElementsByTagName('itemBody')->item(0);
        $mi = $ib->getElementsByTagName('matchInteraction')->item(0);
        $sms = $mi->getElementsByTagName('simpleMatchSet')->item(1);

        foreach ($sms->getElementsByTagName('simpleAssociableChoice') as $simpleLabel) {
            //create a new Label and set attributes
            $label = new Label();
            $label->setValue($this->value($simpleLabel));
            $identifiant = $simpleLabel->getAttribute('identifier');
            $label->setScoreRightResponse($this->notation($identifiant));
            $label->setInteractionMatching($this->interactionMatching);
            $label->setOrdre($ordre);

            if ($simpleLabel->hasAttribute('fixed') && $simpleLabel->getAttribute('fixed') == 'true') {
                $label->setPositionForce(true);
            } else {
                $label->setPositionForce(false);
            }

            //recording in the DBB
            $this->om->persist($label);
            $this->associatedLabels[$identifiant] = $label;
            ++$ordre;
        }
        $this->om->flush();
    }

    /**
     * Create Proposals in BDD.
     */
    protected function createProposals()
    {
        $ordre = 0;
        $ib = $this->assessmentItem->getElementsByTagName('itemBody')->item(0);
        $mi = $ib->getElementsByTagName('matchInteraction')->item(0);
        $sms = $mi->getElementsByTagName('simpleMatchSet')->item(0);
        $labels = $this->associatedLabels;
        $allRelations = $this->relations();

        // foreach proposal into the export file
        foreach ($sms->getElementsByTagName('simpleAssociableChoice') as $simpleProposal) {
            $proposal = new Proposal();
            $proposal->setValue($this->value($simpleProposal));
            $proposal->setOrdre($ordre);

            if ($simpleProposal->hasAttribute('fixed') && $simpleProposal->getAttribute('fixed') == 'true') {
                $proposal->setPositionForce(true);
            } else {
                $proposal->setPositionForce(false);
            }

            $identifiant = $simpleProposal->getAttribute('identifier');
            $proposal->setInteractionMatching($this->interactionMatching);
            $this->om->persist($proposal);
            $rightLabel = 0;
            //compare all relations to the proposal selected
            foreach ($allRelations as $relation) {
                if (stripos($relation, $identifiant) !== false) {
                    $rightLabel = $relation;
                    $rightLabel = str_replace($identifiant, '', $rightLabel);
                    $rightLabel = str_replace(' ', '', $rightLabel);
                }
            }
            // foreach label of the export file, compare to the right relation
            foreach ($labels as $key => $label) {
                if ($key == $rightLabel) {
                    $proposal->addAssociatedLabel($label);
                    $proposal->setInteractionMatching($this->interactionMatching);
                    $this->om->persist($proposal);
                }
            }
            ++$ordre;
        }
        $this->om->flush();
    }

    /**
     * get all relations of the question.
     *
     *
     * @return $allRelations
     */
    protected function relations()
    {
        $rd = $this->assessmentItem->getElementsByTagName('responseDeclaration')->item(0);
        $cr = $rd->getElementsByTagName('mapping')->item(0);
        $allRelations = [];

        foreach ($cr->getElementsByTagName('mapEntry') as $key => $relation) {
            $allRelations[$key] = $relation->getAttribute('mapKey');
        }

        return $allRelations;
    }

    /**
     * Get value of the balise.
     *
     *
     * @param type $balise
     *
     * @return $value
     */
    protected function value($balise)
    {
        $value = $this->domElementToString($balise);
        $value = preg_replace('(<simpleAssociableChoice.*?>)', '', $value);
        $value = str_replace('</simpleAssociableChoice>', '', $value);
        $value = html_entity_decode($value);

        return $value;
    }

    /**
     * Get the score of the relation.
     *
     *
     * @param type $identifiant
     *
     * @return $notation
     */
    protected function notation($identifiant)
    {
        $m = $this->assessmentItem->getElementsByTagName('mapping')->item(0);
        $notation = 0;
        foreach ($m->getElementsByTagName('mapEntry') as $relation) {
            $value = $relation->getAttribute('mapKey');
            //if mapEntry match to identifier add the notation
            if (stripos($value, $identifiant)) {
                $notation = $notation + $relation->getAttribute('mappedValue');
            }
        }

        return $notation;
    }

    /**
     * Get Type Matching.
     */
    protected function matchingType()
    {
        $ri = $this->assessmentItem->getElementsByTagName('responseDeclaration')->item(0);
        if ($ri->hasAttribute('cardinality') && $ri->getAttribute('cardinality') == 'single') {
            //type : to drag
            $type = $this->om
                         ->getRepository('UJMExoBundle:TypeMatching')
                         ->findOneBy(array('code' => 2));
            $this->interactionMatching->setTypeMatching($type);
        } else {
            //type : to bind
            $type = $this->om
                         ->getRepository('UJMExoBundle:TypeMatching')
                         ->findOneBy(array('code' => 1));
            $this->interactionMatching->setTypeMatching($type);
        }
    }
}
