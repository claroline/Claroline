<?php

/**
 * To import an open question.
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\InteractionOpen;

class OpenImport extends QtiImport
{
    protected $interactionOpen;
    protected $codeType;

    /**
     * Implements the abstract method.
     *
     * @param qtiRepository $qtiRepos
     * @param DOMElement    $assessmentItem assessmentItem of the question to imported
     * @param string        $path           parent directory of the files
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem, $path)
    {
        $this->qtiRepos = $qtiRepos;
        $this->path = $path;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);

        if ($this->qtiValidate() === false) {
            return false;
        }

        $this->createQuestion(InteractionOpen::TYPE);
        $this->createInteractionOpen();
    }

    /**
     * Create the InteractionOpen object.
     */
    protected function createInteractionOpen()
    {
        $ocd = $this->assessmentItem->getElementsByTagName('outcomeDeclaration')->item(0);
        $df = $ocd->getElementsByTagName('defaultValue')->item(0);
        $val = $df->getElementsByTagName('value')->item(0);
        $codeTypeOpen = $this->getCodeTypeOpen();

        $this->interactionOpen = new InteractionOpen();
        $this->interactionOpen->setQuestion($this->question);
        $this->interactionOpen->setOrthographyCorrect(false);
        $this->interactionOpen->setTypeOpenQuestion($codeTypeOpen);
        $this->interactionOpen->setScoreMaxLongResp($val->nodeValue);

        $this->om->persist($this->interactionOpen);
        $this->om->flush();
    }

    /**
     * return the TypeOpenQuestion.
     *
     *
     * @return UJM\ExoBundle\Entity\TypeOpenQuestion
     */
    protected function getCodeTypeOpen()
    {
        $type = $this->om
                     ->getRepository('UJMExoBundle:TypeOpenQuestion')
                     ->findOneByCode($this->codeType);

        return $type;
    }

    /**
     * Implements the abstract method.
     */
    protected function getPrompt()
    {
        return $this->getPromptChild();
    }

    /**
     * Implements the abstract method.
     */
    protected function qtiValidate()
    {
        $validated = false;
        switch ($this->getCodeTypeOpen()) {
            case 'numerical':
                $validated = $this->numericalQtiValidate();
                break;
            case 'long':
                $validated = $this->longQtiValidate();
                break;
            case 'short':
                $validated = $this->shortQtiValidate();
                break;
            case 'oneWord':
                $validated = $this->oneWordValidate();
                break;
        }

        return $validated;
    }

    private function numericalQtiValidate()
    {
        return true;
    }

    private function longQtiValidate()
    {
        if ($this->assessmentItem->getElementsByTagName('responseDeclaration')->item(0) == null) {
            return false;
        }

        return true;
    }

    private function shortQtiValidate()
    {
        if ($this->assessmentItem->getElementsByTagName('responseDeclaration')->item(0) == null) {
            return false;
        }
        $rd = $this->assessmentItem->getElementsByTagName('responseDeclaration')->item(0);
        if ($rd->getElementsByTagName('mapping')->item(0) == null) {
            return false;
        }

        return true;
    }

    private function oneWordValidate()
    {
        return $this->shortQtiValidate();
    }
}
