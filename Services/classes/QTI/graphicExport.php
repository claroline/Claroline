<?php

/**
 * To export a graphic question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class graphicExport extends qtiExport
{
    private $interactiongraph;
    private $selectPointInteraction;

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
        $this->promptTag();

        if(($this->interactiongraph->getInteraction()->getFeedBack()!=Null)
                && ($this->interactiongraph->getInteraction()->getFeedBack()!="") ){
            $this->qtiFeedBack($interaction->getFeedBack());
        }

        $this->document->save($this->qtiRepos->getUserDir().$this->question->getId().'_qestion_qti.xml');

        $this->getPicture();

        return $this->getResponse();
    }

    /*Claculate Radius  and x,y of the center of the circle
     * rect: left-x, top-y, right-x, bottom-y.
     * circle: center-x, center-y, radius. Note. When the radius value is a percentage value,
     *
     * @access private
     *
     */
    private function qtiCoord($coords)
    {

        $Coords_value= $coords->getValue();
        $Coords_size = $coords->getSize();
        $radius = $Coords_size/2;
        list($x, $y) = explode(',', $Coords_value);

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
        $taburl = explode('/', $this->interactiongraph->getDocument()->getUrl());
        $pictureName = end($taburl);
        $this->selectPointInteraction = $this->document->createElement("selectPointInteraction");
        $this->selectPointInteraction->setAttribute("responseIdentifier", "RESPONSE");
        $this->selectPointInteraction->setAttribute("maxChoices",
                count($this->interactiongraph->getCoords()));

        $object = $this->document->CreateElement('object');
        $mimetype = $this->interactiongraph->getDocument()->getType();
        if (strpos($mimetype, 'image/') === false) {
            $mimetype = "image/". $mimetype;
        }
        $object->setAttribute("type", $mimetype);
        $object->setAttribute("width", $this->interactiongraph->getWidth());
        $object->setAttribute("height", $this->interactiongraph->getHeight());
        $object->setAttribute("data", $pictureName);
        $objecttxt = $this->document->CreateTextNode($this->interactiongraph->getDocument()->getLabel());
        $object->appendChild($objecttxt);
        $this->selectPointInteraction->appendChild($object);


        $this->itemBody->appendChild($this->selectPointInteraction);
    }

    /**
     * Implements the abstract method
     * add the tag prompt in selectPointInteraction
     *
     * @access protected
     *
     */
    protected function promptTag()
    {
        $prompt = $this->document->CreateElement('prompt');
        $prompttxt = $this->document->CreateTextNode($this->interactiongraph->getInteraction()->getInvite());
        $prompt->appendChild($prompttxt);
        $this->selectPointInteraction->appendChild($prompt);
    }

    /**
     * add the picture in the archive
     *
     * @access private
     *
     */
    private function getPicture()
    {
        $picture = $this->interactiongraph->getDocument();
        $src = $picture->getUrl();
        $taburl = explode('/', $src);
        $pictureName = end($taburl);
        $dest = $this->qtiRepos->getUserDir().$pictureName;
        copy($src, $dest);
        $ressource = array ('name' => $pictureName, 'url' => $dest);
        $this->resourcesLinked[] = $ressource;

    }

}