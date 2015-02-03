<?php

/**
 * To import an open question
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\InteractionOpen;

class openImport extends qtiImport
{
    protected $interactionOpen;

    /**
     * Implements the abstract method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     * @param DOMElement $assessmentItem assessmentItem of the question to imported
     *
     * @return UJM\ExoBundle\Entity\InteractionOpen
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem) {
        $this->qtiRepos = $qtiRepos;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);

        $this->createQuestion();

        $this->createInteraction();
        $this->interaction->setType('InteractionOpen');
        $this->doctrine->getManager()->persist($this->interaction);
        $this->doctrine->getManager()->flush();

        $this->createInteractionOpen();

        return $this->interactionOpen;
    }

    /**
     * Create the InteractionOpen object
     *
     * @access protected
     *
     */
    protected function createInteractionOpen() {
        $ocd = $this->assessmentItem->getElementsByTagName("outcomeDeclaration")->item(0);
        $df = $ocd->getElementsByTagName('defaultValue')->item(0);
        $val = $df->getElementsByTagName('value')->item(0);
        $codeTypeOpen = $this->getCodeTypeOpen();

        $this->interactionOpen = new InteractionOpen();
        $this->interactionOpen->setInteraction($this->interaction);
        $this->interactionOpen->setOrthographyCorrect(FALSE);
        $this->interactionOpen->setTypeOpenQuestion($codeTypeOpen);
        $this->interactionOpen->setScoreMaxLongResp($val->nodeValue);

        $this->doctrine->getManager()->persist($this->interactionOpen);
        $this->doctrine->getManager()->flush();

    }

    /**
     * return the TypeOpenQuestion
     *
     * @access protected
     *
     * @return UJM\ExoBundle\Entity\TypeOpenQuestion
     *
     */
    protected function getCodeTypeOpen() {
        $type =$this->doctrine->getManager()
                ->getRepository('UJMExoBundle:TypeOpenQuestion')
                ->findOneByCode(2);

        return $type;
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
        $eti = $ib->getElementsByTagName("extendedTextInteraction")->item(0);
        if ($eti->getElementsByTagName("prompt")->item(0)) {
            $prompt = $eti->getElementsByTagName("prompt")->item(0);
            $text = $this->domElementToString($prompt);
            $text = str_replace('<prompt>', '', $text);
            $text = str_replace('</prompt>', '', $text);
            $text = html_entity_decode($text);
        }

        return $text;
    }
}
