<?php

/**
 * To export a Matching question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class MatchingExport extends QtiExport
{
    private $matchInteraction;
    private $interactionmatching;
    private $correctResponse;
    private $cardinality;

     /**
     * Implements the abstract method
     *
     * @access public
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param qtiRepository $qtiRepos
     */
    public function export(\UJM\ExoBundle\Entity\Interaction $interaction, qtiRepository $qtiRepos)
    {
        $this->qtiRepos = $qtiRepos;
        $this->question = $interaction->getQuestion();

        $this->interactionmatching = $this->doctrine
                                    ->getManager()
                                    ->getRepository('UJMExoBundle:InteractionMatching')
                                    ->findOneBy(array('interaction' => $interaction->getId()));

        if ($this->interactionmatching->getTypeMatching() == 'To bind') {
            $this->cardinality = 'multiple';
        } else {
            $this->cardinality = 'single';
        }
        $matchingType = 'match';

        $this->qtiHead($matchingType, $this->question->getTitle());
        $this->qtiResponseDeclaration('RESPONSE','identifier', $this->cardinality);
        $this->qtiOutComeDeclaration();

        $this->correctResponseTag();
        $this->itemBodyTag();
        $this->matchingInteractionTag();
        $this->promptTag();

        //comment globale for this question
        if(($this->interactionmatching->getInteraction()->getFeedBack()!=Null)
                && ($this->interactionmatching->getInteraction()->getFeedBack()!="") ){
            $this->qtiFeedBack($interaction->getFeedBack());
        }

        $this->document->save($this->qtiRepos->getUserDir().$this->question->getId().'_qestion_qti.xml');

        return $this->getResponse();

    }

    /**
     * add the tag matchingInteraction in itemBody
     *
     * @access private
     */
    protected function matchingInteractionTag()
    {
        $i=0;
        foreach ($this->interactionmatching->getProposals() as $pr) {
            $i++;
        }
        if($this->cardinality == "multiple") {
            $w=0;
            foreach ($this->interactionmatching->getLabels() as $pr) {
                $w++;
            }
            $maxAssociation = $w * $i;
        } else {
            $maxAssociation = $i;
        }
        if($this->interactionmatching->getShuffle() == 1 ) {
            $shuffle = "true";
        } else {
            $shuffle = "false";
        }
        $this->matchInteraction = $this->document->CreateElement('matchInteraction');
        $this->matchInteraction->setAttribute("responseIdentifier", "RESPONSE");
        $this->matchInteraction->setAttribute("shuffle", $shuffle);
        $this->matchInteraction->setAttribute("maxAssociation", $maxAssociation);
        $this->itemBody->appendChild($this->matchInteraction);

    }

    /**
     * Implements the abstract method
     *
     * @access protected
     */
    protected function promptTag()
    {
        $prompt = $this->document->CreateElement('prompt');
        $this->matchInteraction->appendChild($prompt);

        $prompttxt =  $this->document
                ->CreateTextNode(
                        $this->interactionmatching->getInteraction()->getInvite()
                        );
        $prompt->appendChild($prompttxt);
        $this->qtiProposal();
        $this->qtiLabel();
        $this->notation();
    }

    /**
     * add the simpleMatchSetTag
     *
     * @access protected
     */
    protected function qtiProposal()
    {
        $proposal = $this->document->CreateElement('simpleMatchSet');
        $this->matchInteraction->appendChild($proposal);
        $i=-1;
        foreach ($this->interactionmatching->getProposals() as $pr) {
            $i++;
            //for add proposals
            $this->simpleMatchSetTagProposal($pr, $i, $proposal);
        }
    }

    /**
     * add the simpleAssociableChoiceTag
     *
     * @access protected
     *
     * @param type $proposal
     * @param type $numberProposal
     * @param type $elementProposal
     */
    protected function simpleMatchSetTagProposal($proposal, $numberProposal, $elementProposal)
    {
        //for the maxConnection in the tag simpleAssociableChoice of proposals
        if($this->cardinality == "multiple") {
            $w=0;
            foreach ($this->interactionmatching->getLabels() as $la) {
                $w++;
            }
            $maxAssociation = $w;
        } else {
            $maxAssociation = 1;
        }

        if ($proposal->getPositionForce() == 1) {
            $positionForced = 'true';
        } else {
            $positionForced = 'false';
        }

        $simpleProposal = $this->document->CreateElement('simpleAssociableChoice');

        $simpleProposal->setAttribute("identifier", "left".$numberProposal);
        $simpleProposal->setAttribute("fixed", $positionForced);
        $simpleProposal->setAttribute("matchMax", $maxAssociation);

        $this->matchInteraction->appendChild($simpleProposal);
        $simpleProposaltxt =  $this->document->CreateTextNode($proposal->getValue());

        $simpleProposal->appendChild($simpleProposaltxt);
        $elementProposal->appendChild($simpleProposal);
    }

    /**
     * add the simpleMatchSetTag
     *
     * @access protected
     */
    protected function qtiLabel()
    {
        $label = $this->document->CreateElement('simpleMatchSet');
        $this->matchInteraction->appendChild($label);
        $i=-1;
        foreach ($this->interactionmatching->getLabels() as $la) {
            $i++;
            //for add labels
            $this->simpleMatchSetTagLabel($la, $i, $label);
        }
    }

    /**
     * add the simpleAssociableChoiceTag
     *
     * @access protected
     *
     * @param type $label
     * @param type $numberLabel
     * @param type $elementLabel
     */
    protected function simpleMatchSetTagLabel($label, $numberLabel, $elementLabel)
    {
        if($this->cardinality == "multiple") {
            $w = 0;
            foreach ($this->interactionmatching->getProposals() as $pr) {
                $w++;
            }
            $maxAssociation = $w;
        } else {
            $maxAssociation = 1;
        }

        if ($label->getPositionForce() == 1) {
            $positionForced = 'true';
        } else {
            $positionForced = 'false';
        }

        $simpleLabel = $this->document->CreateElement('simpleAssociableChoice');

        $simpleLabel->setAttribute("identifier", "right".$numberLabel);
        $simpleLabel->setAttribute("fixed", $positionForced);
        $simpleLabel->setAttribute("matchMax", $maxAssociation);

        $this->matchInteraction->appendChild($simpleLabel);
        $simpleLabeltxt =  $this->document->CreateTextNode($label->getValue());

        $simpleLabel->appendChild($simpleLabeltxt);
        $elementLabel->appendChild($simpleLabel);
    }

    /**
     * Implements the abstract method
     * add the tag correctResponse in responseDeclaration
     *
     * @access protected
     */
    protected function correctResponseTag()
    {
        $this->correctResponse = $this->document->CreateElement('correctResponse');
        $this->getAssociationsCorrectResponse($this->correctResponse);
        $this->responseDeclaration[0]->appendChild($this->correctResponse);
    }

    /**
     * for the notation
     *
     * @access protected
     */
    protected function notation()
    {
        $mapping = $this->document->CreateElement('mapping');
        $mapping->setAttribute("defaultValue", "0");
        //get associations
        $this->AssociationsMapEntry($mapping);
        $this->responseDeclaration[0]->appendChild($mapping);
    }

    /**
     * get associations and put it in mapEntry
     *
     * @access protected
     *
     * @param type $mapping
     */
    protected function AssociationsMapEntry($mapping)
    {
        foreach ($this->interactionmatching->getLabels() as $keyLa => $la) {
            $labels[$keyLa] = $la->getId();
            $points[$keyLa] = $la->getScoreRightResponse();
            $nbrLabel[$la->getId()] = 0;
        }
        //recup of associated labels
        $allAssocLabel = $this->AssociatedLabels();
        $nbrLabelAssociated = $this->nbrLabel($nbrLabel, $labels, $allAssocLabel);

        foreach ($allAssocLabel as $key => $assocLabel) {

            foreach ($assocLabel as $label) {

                //recup of each id label of relations
                $associatedLabel = $label->getId();

                //recovery id label of the interaction
                foreach ($labels as $key2 => $la2) {

                    //compare two labels for know the index in mapEntry
                    if($la2 == $associatedLabel) {
                        $mapEntry= $this->document->CreateElement('mapEntry');
                        $mapEntry->setAttribute("mapKey", "left".$key." right".$key2);

                        if ( $nbrLabelAssociated[$la2] == 0 ) {
                            $mapEntry->setAttribute("mappedValue", $points[$key2]);
                        } else {
                            $mapEntry->setAttribute("mappedValue", $points[$key2] / $nbrLabelAssociated[$la2]);
                        }
                        $mapping->appendChild($mapEntry);
                    }
                }
            }
        }
    }

    /**
     * get number of labels for the division of the notation
     *
     * @access protected
     *
     * @param type $nbrLabel
     * @param type $labels
     * @param type $allAssocLabel
     *
     * @return $nbrLabel
     */
    protected function nbrLabel($nbrLabel, $labels, $allAssocLabel)
    {
        foreach ($allAssocLabel as $assocLabel) {

            foreach ($assocLabel as $label) {
                $associatedLabel = $label->getId();

                foreach ($labels as $la2) {

                    if($la2 == $associatedLabel) {
                        $nbrLabel[$la2] = $nbrLabel[$la2] +1;
                    }
                }
            }
        }
        return $nbrLabel;
    }

    /**
     * get associations and put it in value
     *
     * @access protected
     *
     * @param type $elementParent
     */
    protected function getAssociationsCorrectResponse($elementParent)
    {
        foreach ($this->interactionmatching->getLabels() as $keyLa => $la) {
            $labels[$keyLa] = $la->getId();
        }
        $allAssocLabel = $this->AssociatedLabels();

        foreach ($allAssocLabel as $key => $assocLabel) {

            foreach ($assocLabel as $label){
                //to know labels of associatedLabel in the table proposal
                $idAssociatedLabel = $label->getId();

                //to know labels of table label
                foreach ($labels as $key2 => $la2) {

                    //compare two labels for know the index in mapEntry
                    if($la2 == $idAssociatedLabel) {
                        $value= $this->document->CreateElement('value');
                        $valuetxt = $this->document->CreateTextNode("left".$key." right".$key2);
                        $value->appendChild($valuetxt);
                        $elementParent->appendChild($value);
                    }
                }
            }
        }
    }

    /**
     * returns associated labels
     *
     * @access protected
     *
     * @return $assocLabels
     */
    protected function AssociatedLabels()
    {
        foreach ($this->interactionmatching->getProposals() as $key => $la) {
            $assocLabels[$key] = $la->getAssociatedLabel();
        }
        return $assocLabels;
    }
}
