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
    private $coords;
    private $picture;

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

        $this->coords = $this->doctrine->getManager()
                       ->getRepository('UJMExoBundle:Coords')
                       ->findBy(array('interactionGraphic' => $this->interactiongraph->getId()));

        $this->picture = $this->doctrine->getManager()
                          ->getRepository('UJMExoBundle:Document')
                          ->findOneBy(array('id' => $this->interactiongraph->getDocument()));

        if (count($this->coords) > 1 ) {
            $identifier  = 'positionObjects';
            $cardinality = 'multiple';
        } else {
            $identifier  = 'selectPoint';
            $cardinality = 'single';
        }
        $this->qtiHead($identifier, $this->question->getTitle());
        $this->qtiResponseDeclaration('point', $cardinality);
        $this->qtiOutComeDeclaration();

        $this->correctResponseTag();
        $this->areaMappingTag();

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
        foreach ($this->coords as $c) {
            $xy = $this->qtiCoord($c);
            $Tagvalue = $this->document->CreateElement("value");
            $responsevalue = $this->document->CreateTextNode($xy[0]." ".$xy[1]);
            $Tagvalue->appendChild($responsevalue);
            $correctResponse->appendChild($Tagvalue);
        }

        $this->responseDeclaration->appendChild($correctResponse);
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
        $this->responseDeclaration->appendChild($areaMapping);

        foreach ($this->coords as $c) {
            $xy = $this->qtiCoord($c);
            $areaMapEntry = $this->document->createElement("areaMapEntry");
            $areaMapEntry->setAttribute("shape", $c->getShape());
            $areaMapEntry->setAttribute("coords",$xy[0].",".$xy[1].",".$xy[2]);
            $areaMapEntry->setAttribute("mappedValue", $c->getScoreCoords());
            $areaMapping->appendChild($areaMapEntry);
        }
    }

}