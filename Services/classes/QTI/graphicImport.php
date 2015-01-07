<?php

/**
 * To import a QCM question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\InteractionGraphic;

class graphicImport extends qtiImport {

    protected $interactionGraph;

    /**
     * Implements the abstract method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     *
     */
    public function import(qtiRepository $qtiRepos, \DOMDocument $document) {
        $this->qtiRepos = $qtiRepos;
        $this->document = $document;
        $this->getQTICategory();
        $this->initAssessmentItem();

        $this->createQuestion();

        $this->createInteraction();
        $this->interaction->setType('InteractionGraphic');
        $this->doctrine->getManager()->persist($this->interaction);
        $this->doctrine->getManager()->flush();

        $this->createInteractionGraphic();
    }

    /**
     * Create the InteractionGraphic object
     *
     * @access protected
     *
     */
    protected function createInteractionGraphic() {
        $spi = $this->assessmentItem->getElementsByTagName("selectPointInteraction")->item(0);
        $ob = $spi->getElementsByTagName('object')->item(0);

        $this->interactionGraph = new InteractionGraphic();
        $this->interactionGraph->setInteraction($this->interaction);
        $this->interactionGraph->setHeight($ob->getAttribute('height'));
        $this->interactionGraph->setWidth($ob->getAttribute('width'));

        $this->doctrine->getManager()->persist($this->interactionGraph);
        $this->doctrine->getManager()->flush();

        $this->createCoords();
        $this->createPicture();
    }

    /**
     * Create the Coords
     *
     * @access protected
     *
     */
    protected function createCoords() {
        $am = $this->assessmentItem->getElementsByTagName("areaMapping")->item(0);

        foreach ($am->getElementsByTagName("areaMapEntry") as $areaMapEntry) {
            $coords = new Coords();
            $coords->setValue($areaMapEntry->getAttribute('coords'));
            $coords->setShape($areaMapEntry->getAttribute('shape'));
            $coords->setScoreCoords($areaMapEntry->getAttribute('mappedValue'));
            $coords->setColor('white');
            $coords->setInteractionGraphic($this->interactionGraph);
            $this->doctrine->getManager()->persist($coords);
            $this->doctrine->getManager()->flush();
        }
    }

    /**
     * Create the Document
     *
     * @access protected
     *
     */
    protected function createPicture() {

    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function getPrompt()
    {
        $text = '';
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        if ($ib->getElementsByTagName("prompt")->item(0)) {
            $prompt = $ib->getElementsByTagName("prompt")->item(0);
            $text = $this->domElementToString($prompt);
            $text = str_replace('<prompt>', '', $text);
            $text = str_replace('</prompt>', '', $text);
            $text = html_entity_decode($text);
        }

        return $text;
    }
}
