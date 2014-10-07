<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * To export a graphic question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class graphicExport extends qtiExport
{
    private $interactiongraph;

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

        $this->interactiongraph = $this->doctrine
                                ->getManager()
                                ->getRepository('UJMExoBundle:InteractionGraphic')
                                ->findOneBy(array('interaction' => $interaction->getId()));

        if (count($this->interactiongraph->getCoords()) > 1 ) {
            $cardinality = 'multiple';
        } else {
            $cardinality = 'single';
        }
        $this->qtiHead('selectPoint', $this->question->getTitle());
        $this->qtiResponseDeclaration('RESPONSE', 'point', $cardinality);
        $this->qtiOutComeDeclaration();

        $this->correctResponseTag();
        $this->areaMappingTag();
        $this->itemBodyTag();
        $this->selectPointInteractionTag();
        
        if(($this->interactiongraph->getInteraction()->getFeedBack()!=Null) 
                && ($this->interactiongraph->getInteraction()->getFeedBack()!="") ){
            $this->qtiFeedBack($interaction->getFeedBack());
        }

        $this->document->save($this->userDir.'testfile.xml');

        return $this->getResponse();
    }

    /*Claculate Radius  and x,y of the center of the circle
     * rect: left-x, top-y, right-x, bottom-y.
     * circle: center-x, center-y, radius. Note. When the radius value is a percentage value,
     *
     * @access public
     *
     */
    private function qtiCoord($coords)
    {

        $Coords_value= $coords->getValue();
        $Coords_size = $coords->getSize();
        $radius = $Coords_size/2;
        list($x, $y) = split('[,]', $Coords_value);

        $x_center_circle = $x + ($radius);
        $y_center_circle = $y + ($radius);

        return array($x_center_circle, $y_center_circle, $radius);
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
        $correctResponse = $this->document->createElement("correctResponse");
        foreach ($this->interactiongraph->getCoords() as $c) {
            $xy = $this->qtiCoord($c);
            $Tagvalue = $this->document->CreateElement("value");
            $responsevalue = $this->document->CreateTextNode($xy[0]." ".$xy[1]);
            $Tagvalue->appendChild($responsevalue);
            $correctResponse->appendChild($Tagvalue);
        }

        $this->responseDeclaration[0]->appendChild($correctResponse);
    }

    /**
     * add the tag areaMapping in responseDeclaration
     *
     * @access private
     *
     */
    private function areaMappingTag()
    {
        $areaMapping = $this->document->createElement("areaMapping");
        $areaMapping->setAttribute("defaultValue", "0");
        $this->responseDeclaration[0]->appendChild($areaMapping);

        foreach ($this->interactiongraph->getCoords() as $c) {
            $xy = $this->qtiCoord($c);
            $areaMapEntry = $this->document->createElement("areaMapEntry");
            $areaMapEntry->setAttribute("shape", $c->getShape());
            $areaMapEntry->setAttribute("coords",$xy[0].",".$xy[1].",".$xy[2]);
            $areaMapEntry->setAttribute("mappedValue", $c->getScoreCoords());
            $areaMapping->appendChild($areaMapEntry);
        }
    }

    /**
     * add the tag selectPointInteractionTag in itemBody
     *
     * @access private
     *
     */
    private function selectPointInteractionTag()
    {
        $selectPointInteraction = $this->document->createElement("selectPointInteraction");
        $selectPointInteraction->setAttribute("responseIdentifier", "RESPONSE");
        $selectPointInteraction->setAttribute("maxChoices",
                count($this->interactiongraph->getCoords()));

        $prompt = $this->document->CreateElement('prompt');
        $prompttxt = $this->document->CreateTextNode($this->interactiongraph->getInteraction()->getInvite());
        $prompt->appendChild($prompttxt);
        $selectPointInteraction->appendChild($prompt);

        $object = $this->document->CreateElement('object');
        $object->setAttribute("type", "image/". $this->interactiongraph->getDocument()->getType());
        $object->setAttribute("width", $this->interactiongraph->getWidth());
        $object->setAttribute("height", $this->interactiongraph->getHeight());
        $object->setAttribute("data", $this->interactiongraph->getDocument()->getUrl());
        $objecttxt = $this->document->CreateTextNode($this->interactiongraph->getDocument()->getLabel());
        $object->appendChild($objecttxt);
        $selectPointInteraction->appendChild($object);


        $this->itemBody->appendChild($selectPointInteraction);
    }

}