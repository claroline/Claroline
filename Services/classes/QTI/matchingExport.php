<?php

/**
 * To export a Matching question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class matchingExport extends qtiExport
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
     *
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

        //$this->defaultValueTag();
        $this->correctResponseTag();
        $this->itemBodyTag();
        $this->matchingInteractionTag();
        $this->promptTag();

        //comment globale for this question
        if(($this->interactionmatching->getInteraction()->getFeedBack()!=Null)
                && ($this->interactionmatching->getInteraction()->getFeedBack()!="") ){
            $this->qtiFeedBack($interaction->getFeedBack());
        }

        $this->document->save($this->qtiRepos->getUserDir().'testfile.xml');

        return $this->getResponse();

    }

    /**
     * add the tag matchingInteraction in itemBody
     *
     * @access private
     *
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
        $this->matchInteraction = $this->document->CreateElement('matchInteraction');
        $this->matchInteraction->setAttribute("directedPair", "RESPONSE");
        //shuffle always false because no implements now
        $this->matchInteraction->setAttribute("shuffle", "false");
        $this->matchInteraction->setAttribute("maxAssociation", $maxAssociation);
        $this->itemBody->appendChild($this->matchInteraction);

    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
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
            $this->simpleMatchSetTagProposal($pr, $i, $proposal);
        }
    }

    /**
     * add the simpleAssociableChoiceTag
     *
     * @param type $proposal
     * @param type $numberProposal
     * @param type $elementProposal
     *
     * @access protected
     */
    protected function simpleMatchSetTagProposal($proposal, $numberProposal, $elementProposal)
    {
        //for the maxConnection in the tag simpleAssociableChoice of proposals
        if($this->cardinality == "multiple") {
            $w=0;
            foreach ($this->interactionmatching->getLabels() as $pr) {
                $w++;
            }
            $maxAssociation = $w;
        } else {
            $maxAssociation = 1;
        }
        $simpleProposal = $this->document->CreateElement('simpleAssociableChoice');
        $simpleProposal->setAttribute("identifier", "left".$numberProposal);
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
            $this->simpleMatchSetTagLabel($la, $i, $label);
        }
    }

    /**
     * add the simpleAssociableChoiceTag
     *
     * @param type $label
     * @param type $numberLabel
     * @param type $elementLabel
     *
     * @access protected
     */
    protected function simpleMatchSetTagLabel($label, $numberLabel, $elementLabel)
    {
        if($this->cardinality == "multiple") {
            $w=0;
            foreach ($this->interactionmatching->getLabels() as $pr) {
                $w++;
            }
            $maxAssociation = $w;
        } else {
            $maxAssociation = 1;
        }
        $simpleLabel = $this->document->CreateElement('simpleAssociableChoice');
        $simpleLabel->setAttribute("identifier", "right".$numberLabel);
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
     *
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
        $this->getAssociationsMapEntry($mapping);
        $this->responseDeclaration[0]->appendChild($mapping);
    }

    /**
     * get associations and put it in mapEntry
     *
     * @access protected
     *
     * @param type $mapping
     */
    protected function getAssociationsMapEntry($mapping)
    {
        $labels = [];
        $points = [];
        foreach ($this->interactionmatching->getLabels() as $keyLa => $la) {
            $labels[$keyLa] = $la->getId();
            $points[$keyLa] = $la->getScoreRightResponse();
        }
        $proposals = $this->getAssociatedLabels();

//        $this->relations();

        foreach ($proposals as $key => $pr2) {

            foreach ($pr2 as $test) {
                //recup of each id label of relations
                $associatedLabel = $test->getId();
                //recup id label of the interaction
                foreach ($labels as $key2 => $la2) {
                    //compare two labels for know the index for 'rigth...' in mapEntry
                    if($la2 == $associatedLabel) {

                        $mapEntry= $this->document->CreateElement('mapEntry');
                        $mapEntry->setAttribute("mapKey", "left".$key." right".$key2);

//                        if ($oldAssociate == $associatedLabel) {
//
//                            $mapEntry->setAttribute("mappedValue", $points[$key2]/2);
//                        } else {

                            $mapEntry->setAttribute("mappedValue", $points[$key2]);
//                        }
                        $mapping->appendChild($mapEntry);
                    }
                }
            }
        }
    }

    protected function relations()
    {
        foreach ($this->interactionmatching->getProposals() as $keyPr => $pr) {
            $proposals[$keyPr] = $pr->getAssociatedLabel();

            foreach ($proposals as $key => $pr2) {
                foreach ($pr2 as $coucou => $test) {
                    $associatedLabel[$coucou] = $test->getId();
                    $supertest = $pr->getAssociatedLabel($test->getId());
                    var_dump($supertest);
                }

            }
        }
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
        $labels = [];
        foreach ($this->interactionmatching->getLabels() as $keyLa => $la) {
            $labels[$keyLa] = $la->getId();
        }
        $proposals = $this->getAssociatedLabels();
        foreach ($proposals as $key => $pr2) {
            foreach ($pr2 as $test){
                //to know labels of associatedLabel in the table proposal
                $associatedLabel = $test->getId();
                //to know labels of table label
                foreach ($labels as $key2 => $la2) {
                    //compare two labels for know the index for 'rigth...' in mapEntry
                    if($la2 == $associatedLabel) {
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
     * @return $proposals
     */
    protected function getAssociatedLabels()
    {
        $proposals = [];
        foreach ($this->interactionmatching->getProposals() as $keyPr => $pr) {
            $proposals[$keyPr] = $pr->getAssociatedLabel();
        }
        return $proposals;

    }
}