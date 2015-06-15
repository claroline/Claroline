<?php

/**
 * To import an open question
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\InteractionOpen;

class OpenImport extends QtiImport
{
    protected $interactionOpen;
    protected $codeType;

    /**
     * Implements the abstract method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     * @param DOMElement $assessmentItem assessmentItem of the question to imported
     *
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem) {
        $this->qtiRepos = $qtiRepos;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);

        $this->createQuestion();

        $this->createInteraction();
        $this->interaction->setType('InteractionOpen');
        $this->om->persist($this->interaction);
        $this->om->flush();

        $this->createInteractionOpen();
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

        $this->om->persist($this->interactionOpen);
        $this->om->flush();

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
        $type = $this->om
                     ->getRepository('UJMExoBundle:TypeOpenQuestion')
                     ->findOneByCode($this->codeType);

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

        return $this->getPromptChild();
    }
}
