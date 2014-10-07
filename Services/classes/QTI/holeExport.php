<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * To export question with holes in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class holeExport extends qtiExport
{
    private $interactionhole;
    private $correctResponse = array();

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

        $this->interactionhole = $this->doctrine
                                ->getManager()
                                ->getRepository('UJMExoBundle:InteractionHole')
                                ->findOneBy(array('interaction' => $interaction->getId()));

        $this->qtiHead('textEntry', $this->question->getTitle());
        foreach($this->interactionhole->getHoles() as $hole) {
            $this->qtiResponseDeclaration('RESPONSE'.$this->nbResponseDeclaration, 'string', 'single');
            $this->correctResponseTag();
            $this->mappingTag($hole);
        }
        $this->qtiOutComeDeclaration();

        $this->itemBodyTag();
        //$this->mappingTag();

        $this->document->save($this->userDir.'testfile.xml');

        return $this->getResponse();
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
        $responseDeclaration = $this->responseDeclaration[$this->nbResponseDeclaration - 1];
        $this->correctResponse[$this->nbResponseDeclaration - 1] = $this->document->CreateElement('correctResponse');
        $responseDeclaration->appendChild($this->correctResponse[$this->nbResponseDeclaration - 1]);
    }

    /**
     * add the tag mapping in responseDeclaration
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Hole $hole
     *
     */
    private  function mappingTag($hole)
    {
        $responseDeclaration = $this->responseDeclaration[$this->nbResponseDeclaration - 1];
        $correctResponse = $this->correctResponse[$this->nbResponseDeclaration - 1];
        $mapping = $this->document->createElement("mapping");
        $mapping->setAttribute("defaultValue", "0");

        foreach ($hole->getWordResponses() as $resp) {
            $Tagvalue = $this->document->CreateElement("value");
            $responsevalue =  $this->document->CreateTextNode($resp->getResponse());
            $Tagvalue->appendChild($responsevalue);
            $correctResponse->appendChild($Tagvalue);
            $responseDeclaration->appendChild($correctResponse);

            $mapEntry =  $this->document->createElement("mapEntry");
            $mapEntry->setAttribute("mapKey", $resp->getResponse());
            $mapEntry->setAttribute("mappedValue",$resp->getScore());
            $mapping->appendChild($mapEntry);
        }
    }

    /**
     * Description
     *
     * @access private
     *
     */
    private function aNomer()
    {
        $this->responseDeclaration->appendChild($mapping);

        //change the tag <input....> by <inputentry.....>
                           $qst = $this->interactionhole->getHtmlWithoutValue();
                           $regex = '(<input\\s+id="\d+"\\s+class="blank"\\s+name="blank_\d+"\\s+size="\d+"\\s+type="text"\\s+value=""\\s+\/>)';
                           $result = preg_replace($regex, '<textEntryInteraction responseIdentifier="RESPONSE" expectedLength="15"/>', $qst);
                    $objecttxt =  $this->document->CreateTextNode($result);
                    $this->itemBody->appendChild($objecttxt);
    }
}
