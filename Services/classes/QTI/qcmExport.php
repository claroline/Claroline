<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class qcmExport extends qtiExport
{

    private $interactionqcm;
    private $choiceInteraction;
    private $resources_node;
    private $correctResponse;
    private $itemBody;
    private $responseProcessing;

    /**
     * Implements the abstract method
     *
     * @access public
     * @param String \UJM\ExoBundle\Entity\Interaction $interaction
     *
     */
    public function export(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $this->question = $interaction->getQuestion();

        $this->interactionqcm = $this->doctrine
                                ->getManager()
                                ->getRepository('UJMExoBundle:InteractionQCM')
                                ->findOneBy(array('interaction' => $interaction->getId()));

        //if it's Null mean "Global notation for QCM" Else it's Notation for each choice
        $weightresponse = $this->interactionqcm->getWeightResponse();
        if ($this->interactionqcm->getTypeQCM() == 'Multiple response') {
            $choiceType = 'choiceMultiple';
            $cardinality = 'multiple';
        } else {
            $choiceType = 'choice';
            $cardinality = 'single';
        }

        $this->resourcesLinked();

        $this->qtiHead($choiceType, $this->question->getTitle());
        $this->qtiResponseDeclaration('identifier', $cardinality);
        $this->qtiOutComeDeclaration();

        $this->defaultValueTag();
        $this->correctResponseTag();
        $this->itemBodyTag();
        $this->choiceInteractionTag();
        $this->promptTag();

        //comment globale for this question
        if(($interaction->getFeedBack()!=Null) && ($interaction->getFeedBack()!="") ){
            $this->qtiFeedBack($interaction->getFeedBack());
        }

        if($weightresponse == false){
            $this->node->appendChild($this->responseProcessing);
        }

        $this->document->save($this->userDir.'testfile.xml');

        return $this->getResponse();

    }

    private function qtiChoicesQCM()
    {
        $mapping = $this->document->CreateElement('mapping');
        $i=-1;
        //$Alphabets = array('A','B','C','D','E','F','G','H','I','G','K','L');
        foreach($this->interactionqcm->getChoices() as $ch) {

           $i++;
           if($ch->getRightResponse() ==  true) {
               $this->valueCorrectResponseTag($i);
           }

           if($this->interactionqcm->getWeightResponse()==true) {
               $this->notationByChoice($mapping, $i, $ch->getWeight());
           } else {
               $this->notationGlobal();
           }

           $this->simpleChoiceTag($ch, $i);

        }
    }

    private function resourcesLinked()
    {
        // Search for the ID of the ressource from the Invite colonne
        $txt  = $this->interactionqcm->getInteraction()->getInvite();

        $this->path_img="";

        $dom2 = new \DOMDocument();
        $dom2->loadHTML(html_entity_decode($txt));
        $listeimgs = $dom2->getElementsByTagName("img");
        foreach($listeimgs as $img)
        {
          if ($img->hasAttribute("src")) {
             $src= $img->getAttribute("src");
             $id_node= substr($src, 47);
             $resources_file = $this->doctrine
                           ->getManager()
                           ->getRepository('ClarolineCoreBundle:Resource\File')->findBy(array('resourceNode' => $id_node));
             $this->resources_node = $this->doctrine
                           ->getManager()
                           ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find($id_node);
             $this->path_img = $this->container->getParameter('claroline.param.files_directory').'/'.$resources_file[0]->getHashName();
          }

        }
    }

    /**
     * add the tag defaultValue in outcomeDeclaration
     *
     * @access private
     *
     */
    private function defaultValueTag()
    {
        //add the tag <Default value> to the item <outcomeDeclaration>
        $defaultValue = $this->document->CreateElement('defaultValue');
        $this->outcomeDeclaration->appendChild($defaultValue);
        $value = $this->document->CreateElement("value");
        $prompttxt =  $this->document->CreateTextNode("0");
        $value->appendChild($prompttxt);
        $defaultValue->appendChild($value);
    }

    /**
     * add the tag correctResponse in responseDeclaration
     *
     * @access private
     *
     */
    private function correctResponseTag()
    {
        $this->correctResponse = $this->document->CreateElement('correctResponse');
        $this->responseDeclaration->appendChild($this->correctResponse);
    }

    /**
     * add tag value in correctResponse for each good choice
     *
     * @param Integer  $choiceNumber
     *
     * @access private
     *
     */
    private function valueCorrectResponseTag($choiceNumber)
    {
        $value = $this->document->CreateElement('value');
        $this->correctResponse->appendChild($value);
        $valuetxt =  $this->document->CreateTextNode("Choice".$choiceNumber);
        $value->appendChild($valuetxt);
    }

    /**
     * add the tag itemBody in node
     *
     * @access private
     *
     */
    private function itemBodyTag()
    {
        $this->itemBody = $this->document->CreateElement('itemBody');
        $this->node->appendChild($this->itemBody);
    }

    /**
     * add the tag choiceInteraction in itemBody
     *
     * @access private
     *
     */
    private function choiceInteractionTag()
    {
        $this->choiceInteraction = $this->document->CreateElement('choiceInteraction');
        $this->choiceInteraction->setAttribute("responseIdentifier", "RESPONSE");
        if($this->interactionqcm->getShuffle()==1){
            $boolval = "true";
        } else {
            $boolval = "false";
        }

        $this->choiceInteraction->setAttribute("shuffle",$boolval);
        $this->choiceInteraction->setAttribute("maxChoices", "1");
        $this->itemBody->appendChild($this->choiceInteraction);
    }

    /**
     * add the tag prompt in choiceInteraction
     *
     * @access private
     *
     */
    private function promptTag()
    {
        $prompt = $this->document->CreateElement('prompt');
        $this->choiceInteraction->appendChild($prompt);

        //Code pour eliminer du code html sauf la balise img
        $res1 =strip_tags($this->interactionqcm->getInteraction()->getInvite(), '<img>');
        if(!empty($this->path_img)){
            //expression regulière pour eliminer tous les attributs des balises
            $reg="#(?<=\<img)\s*[^>]*(?=>)#";
            $res1=preg_replace($reg,"",$res1);
            //rajouter src de l'image
            $res1= str_replace("<img>", "<img src=\"".$this->resources_node->getName()."\" alt=\"\" />",$res1);
            //generate the mannifest file
            $this->generate_imsmanifest_File($this->resources_node->getName());
        }

        $prompttxt =  $this->document->CreateTextNode(html_entity_decode($res1));
        $prompt->appendChild($prompttxt);
        $this->qtiChoicesQCM($this->correctResponse);
    }

    /**
     * add the tag simpleChoice in choiceInteraction
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Choice $choice
     * @param Integer $choiceNumber
     *
     */
    private function simpleChoiceTag($choice, $choiceNumber)
    {
        $simpleChoice = $this->document->CreateElement('simpleChoice');
        $simpleChoice->setAttribute("identifier", "Choice".$choiceNumber);
        $this->choiceInteraction->appendChild($simpleChoice);
        $simpleChoicetxt =  $this->document->CreateTextNode(strip_tags($choice->getLabel(),'<img>'));
        $simpleChoice->appendChild($simpleChoicetxt);

        //comment per line for each choice
        if(($choice->getFeedback()!=Null) && ($choice->getFeedback()!="")){
            $feedbackInline = $this->document->CreateElement('feedbackInline');
            $feedbackInline->setAttribute("outcomeIdentifier", "FEEDBACK");
            $feedbackInline->setAttribute("identifier","Choice".$choiceNumber);
            $feedbackInline->setAttribute("showHide","show");
            $feedbackInlinetxt = $this->document->CreateTextNode($choice->getFeedback());
            $feedbackInline->appendChild($feedbackInlinetxt);
            $simpleChoice->appendChild($feedbackInline);
        }
    }

    /**
     * add the tags for notation by choice
     *
     * @access private
     *
     * @param DOM element $mapping
     * @param Integer $i
     * @param Float $weight
     *
     */
    private function notationByChoice($mapping, $i, $weight)
    {
       $mapEntry= $this->document->CreateElement('mapEntry');
       $mapEntry->setAttribute("mapKey", "Choice".$i);
       $mapEntry->setAttribute("mappedValue", $weight);
       $mapping->appendChild($mapEntry);
       $this->responseDeclaration->appendChild($mapping);
    }

    /**
     * add the tags for a global notation
     *
     * @access private
     *
     */
    private function notationGlobal()
    {
       $this->responseProcessing =  $this->document->CreateElement('responseProcessing');
       $responseCondition = $this->document->CreateElement('responseCondition');
       $responseIf = $this->document->CreateElement('responseIf');
       $responseElse = $this->document->CreateElement('responseElse');
       $match = $this->document->CreateElement('match');
       $variable = $this->document->CreateElement('variable');
       $variable->setAttribute("identifier", "RESPONSE");
       $correct = $this->document->CreateElement('correct');
       $correct->setAttribute("identifier", "RESPONSE");

       $match->appendChild($variable);
       $match->appendChild($correct);

       $setOutcomeValue = $this->document->CreateElement('setOutcomeValue');
       $setOutcomeValue->setAttribute("identifier", "SCORE");

       $baseValue= $this->document->CreateElement('baseValue');
       $baseValue->setAttribute("baseType", "float");
       $baseValuetxt = $this->document->CreateTextNode($this->interactionqcm->getScoreRightResponse());
       $baseValue->appendChild($baseValuetxt);

       $responseIf->appendChild($match);
       $setOutcomeValue->appendChild($baseValue);
       $responseIf->appendChild($setOutcomeValue);

       ////
       $setOutcomeValue = $this->document->CreateElement('setOutcomeValue');
       $setOutcomeValue->setAttribute("identifier", "SCORE");

       $baseValue= $this->document->CreateElement('baseValue');
       $baseValue->setAttribute("baseType", "float");
       $baseValuetxt = $this->document->CreateTextNode($this->interactionqcm->getScoreFalseResponse());
       $baseValue->appendChild($baseValuetxt);


       $setOutcomeValue->appendChild($baseValue);
       $responseElse->appendChild($setOutcomeValue);


       $responseCondition->appendChild($responseIf);
       $responseCondition->appendChild($responseElse);

       $this->responseProcessing->appendChild($responseCondition);
    }

}