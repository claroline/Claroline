<?php

/**
 * To import a question with holes in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Hole;
use UJM\ExoBundle\Entity\InteractionHole;
use UJM\ExoBundle\Entity\WordResponse;

class holeImport extends qtiImport
{
    protected $interactionHole;
    protected $qtiTextWithHoles;
    protected $textHtml;

    /**
     * Implements the abstract method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     *
     */
    public function import(qtiRepository $qtiRepos, \DOMDocument $document)
    {
        $this->qtiRepos = $qtiRepos;
        $this->document = $document;
        $this->getQTICategory();
        $this->initAssessmentItem();

        $this->createQuestion();

        $this->createInteraction();
        $this->interaction->setType('InteractionHole');
        $this->doctrine->getManager()->persist($this->interaction);
        $this->doctrine->getManager()->flush();

        $this->createInteractionHole();
    }

    /**
     * Create the InteractionHole object
     *
     * @access protected
     *
     */
    protected function createInteractionHole()
    {
        $this->interactionHole = new InteractionHole();
        $this->interactionHole->setInteraction($this->interaction);

        $this->getQtiTextWithHoles();
        $this->getHtml();
        $this->getHtmlWithoutValue();

        $this->doctrine->getManager()->persist($this->interactionHole);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Get property html
     *
     * @access protected
     *
     */
    protected function getHtml()
    {
        $this->textHtml = $this->qtiTextWithHoles;
        $newId = 1;
        $regex = '(<textEntryInteraction.*?>)';
        preg_match_all($regex, $this->qtiTextWithHoles, $matches);
        foreach ($matches[0] as $matche) {
            $tabMatche = explode('"', $matche);
            $responseIdentifier = $tabMatche[1];
            $expectedLength     = $tabMatche[3];
            $correctResponse    = $this->getCorrectResponse($responseIdentifier);
            if (substr($matche, 1, 20) == 'textEntryInteraction') {
                $text = str_replace('textEntryInteraction', 'input', $matche);
                $text = str_replace('responseIdentifier="'.$responseIdentifier.'"', 'id="blank_'.$newId.'" class="blank" autocomplete="off" name="blank_'.$newId.'"', $text);
                $text = str_replace('expectedLength="'.$expectedLength.'"', 'size="'.$expectedLength.'" type="text" value="'.$correctResponse.'"', $text);
            }
            $newId++;
            $this->textHtml = str_replace($matche, $text, $this->textHtml);
        }
        $this->interactionHole->setHtml($this->textHtml);
    }

    /**
     * Get correctResponse
     *
     * @access protected
     *
     * @param String $identifier identifier of hole
     *
     */
    protected function getCorrectResponse($identifier)
    {
        $correctResponse = '';
        foreach($this->assessmentItem->getElementsByTagName("responseDeclaration") as $rp) {
            if ($rp->getAttribute("identifier") == $identifier) {
                $correctResponse = $rp->getElementsByTagName("correctResponse")
                                      ->item(0)->getElementsByTagName("value")
                                      ->item(0)->nodeValue;
            }
        }

        return $correctResponse;
    }

    /**
     * Get property htmlWithoutValue
     *
     * @access protected
     *
     */
    protected function getHtmlWithoutValue()
    {
        $htmlWithoutValue = $this->textHtml;
        $regex = '(<input.*?class="blank".*?>)';
        preg_match_all($regex, $htmlWithoutValue, $matches);
        foreach ($matches[0] as $matche) {
            if (substr($matche, 1, 5) == 'input') {
                $tabMatche = explode('"', $matche);
                $value = $tabMatche[13];
                $inputWithoutValue = str_replace('value="'.$value.'"', 'value=""', $matche);
                $htmlWithoutValue = str_replace($matche, $inputWithoutValue, $htmlWithoutValue);
            }
        }
        $this->interactionHole->sethtmlWithoutValue($htmlWithoutValue);
    }

    /**
     * Create holes
     *
     * @access protected
     *
     */
    protected function createHoles()
    {

    }

    /**
     * Create wordResponse
     *
     * @access protected
     *
     */
    protected function createWordResponse()
    {

    }

    /**
     * Get qtiTextWithHoles
     *
     * @access protected
     *
     */
    protected function getQtiTextWithHoles()
    {
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        $text = $this->document->saveXML($ib);
        $text = str_replace('<itemBody>', '', $text);
        $text = str_replace('</itemBody>', '', $text);
        $text = trim($text);
        $this->qtiTextWithHoles = $text;
    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function getPrompt()
    {
        $prompt = '';
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        if ($ib->getElementsByTagName("prompt")->item(0)) {
            $prompt = $ib->getElementsByTagName("prompt")->item(0)->nodeValue;
            $ib->removeChild($ib->getElementsByTagName("prompt")->item(0));
        }

        return $prompt;
    }
}
