<?php

/**
 * To import a QCM question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Entity\InteractionQCM;

class qcmImport extends qtiImport {

    protected $interactionQCM;

    /**
     * Implements the abstract method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     * @param DOMElement $assessmentItem assessmentItem of the question to imported
     *
     * @return UJM\ExoBundle\Entity\InteractionQCM
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem) {
        $this->qtiRepos = $qtiRepos;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);

        $this->createQuestion();

        $this->createInteraction();
        $this->interaction->setType('InteractionQCM');
        $this->om->persist($this->interaction);
        $this->om->flush();

        $this->createInteractionQCM();

        return $this->interactionQCM;
    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function getPrompt() {
        $prompt = '';
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        $ci = $ib->getElementsByTagName("choiceInteraction")->item(0);
        $text = '';
        if ($ci->getElementsByTagName("prompt")->item(0)) {
            //$prompt = $ci->getElementsByTagName("prompt")->item(0)->nodeValue;

            $prompt = $ci->getElementsByTagName("prompt")->item(0);
            $text = $this->domElementToString($prompt);
            $text = str_replace('<prompt>', '', $text);
            $text = str_replace('</prompt>', '', $text);
            $text = html_entity_decode($text);
        }

        return $text;
    }

    /**
     * Create the InteractionQCM object
     *
     * @access protected
     *
     */
    protected function createInteractionQCM() {
        $rp = $this->assessmentItem->getElementsByTagName("responseProcessing");
        $this->interactionQCM = new InteractionQCM();
        $this->interactionQCM->setInteraction($this->interaction);
        $this->getShuffle();
        $this->getQCMType();
        if ($rp->item(0) && $rp->item(0)->getElementsByTagName("responseCondition")->item(0)) {
            $this->interactionQCM->setWeightResponse(false);
            $this->getGlobalScore();
        } else {
            $this->interactionQCM->setWeightResponse(true);
        }
        $this->om->persist($this->interactionQCM);
        $this->om->flush();
        $this->createChoices();
    }

    /**
     * Get shuffle
     *
     * @access protected
     *
     */
    protected function getShuffle() {
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        $ci = $ib->getElementsByTagName("choiceInteraction")->item(0);
        if ($ci->hasAttribute("shuffle") && $ci->getAttribute("shuffle") == 'true') {
            $this->interactionQCM->setShuffle(TRUE);
        } else {
            $this->interactionQCM->setShuffle(FALSE);
        }
    }

    /**
     * Get Type QCM
     *
     * @access protected
     *
     */
    protected function getQCMType() {
        $ri = $this->assessmentItem->getElementsByTagName("responseDeclaration")->item(0);
        if ($ri->hasAttribute("cardinality") && $ri->getAttribute("cardinality") == 'multiple') {
            $type = $this->om
                         ->getRepository('UJMExoBundle:TypeQCM')
                         ->findOneBy(array('code' => 1));

            $this->interactionQCM->setTypeQCM($type);
        } else {
            $type = $this->om
                         ->getRepository('UJMExoBundle:TypeQCM')
                         ->findOneBy(array('code' => 2));

            $this->interactionQCM->setTypeQCM($type);
        }
    }

    /**
     * Create choices
     *
     * @access protected
     *
     */
    protected function createChoices() {
        $order = 1;
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        $ci = $ib->getElementsByTagName("choiceInteraction")->item(0);

        foreach ($ci->getElementsByTagName("simpleChoice") as $simpleChoice) {
            $choice = new Choice();
            if ($simpleChoice->hasAttribute("fixed") && $simpleChoice->getAttribute("fixed") == 'true') {
                $choice->setPositionForce(true);
            }
            $feedback = $simpleChoice->getElementsByTagName("feedbackInline");
            if ($feedback->item(0)) {
                $choice->setFeedback($feedback->item(0)->nodeValue);
                $simpleChoice->removeChild($feedback->item(0));
            }
            $choice->setLabel($this->choiceValue($simpleChoice));
            $choice->setOrdre($order);
            $choice->setWeight($this->getWeightChoice($simpleChoice->getAttribute("identifier")));
            $choice->setRightResponse($this->getRightResponse($simpleChoice->getAttribute("identifier")));
            $choice->setInteractionQCM($this->interactionQCM);
            $this->om->persist($choice);
            $order ++;
        }
        $this->om->flush();
    }

    /**
     * @access protected
     *
     * @param DOMNodelist::item $simpleChoice element simpleChoice
     *
     * return String $value
     */
    protected function choiceValue($simpleChoice) {
        $value = $this->domElementToString($simpleChoice);
        //$value = str_replace('<simpleChoice>', '', $value);
        $value = preg_replace('(<simpleChoice.*?>)', '', $value);
        $value = str_replace('</simpleChoice>', '', $value);
        $value = html_entity_decode($value);

        return $value;
    }

    /**
     * Get weightChoice
     *
     * @access protected
     *
     * @param String $identifier identifier of choice in the qti file
     *
     * return float
     */
    protected function getWeightChoice($identifier) {
        $weight = 0;
        $ri = $this->assessmentItem->getElementsByTagName("responseDeclaration")->item(0);
        $mapping = $ri->getElementsByTagName("mapping");
        if ($mapping->item(0)) {
            $mps = $mapping->item(0)->getElementsByTagName("mapEntry");
            foreach ($mps as $mp) {
                if ($mp->hasAttribute("mappedValue") && $mp->hasAttribute("mapKey") && $mp->getAttribute("mapKey") == $identifier) {
                    $weight = $mp->getAttribute("mappedValue");
                    break;
                }
            }
        }

        return $weight;
    }

    /**
     * Get rightResponse
     *
     * @access protected
     *
     * @param String $identifier identifier of choice in the qti file
     *
     * return boolean
     */
    protected function getRightResponse($identifier) {
        $rightResponse = false;
        $ri = $this->assessmentItem->getElementsByTagName("responseDeclaration")->item(0);
        $cr = $ri->getElementsByTagName("correctResponse")->item(0);
        $values = $cr->getElementsByTagName("value");
        foreach ($values as $value) {
            if ($identifier == $value->nodeValue) {
                $rightResponse = true;
                break;
            }
        }

        return $rightResponse;
    }

    /**
     * Get score for the right response and the false response
     *
     * @access protected
     *
     */
    protected function getGlobalScore() {
        $rp = $this->assessmentItem->getElementsByTagName("responseProcessing")->item(0);
        $responsesIf = $rp->getElementsByTagName("responseIf");
        foreach ($responsesIf as $ri) {
            if ($ri->getElementsByTagName("match")->item(0) != null) {
                $val = $ri->getElementsByTagName("baseValue")->item(0)->nodeValue;
                $this->interactionQCM->setScoreRightResponse($val);
            }
        }
        $reponseEsle = $rp->getElementsByTagName("responseElse")->item(0);
        $val = $reponseEsle->getElementsByTagName("baseValue")->item(0)->nodeValue;
        $this->interactionQCM->setScoreFalseResponse($val);
    }

}
