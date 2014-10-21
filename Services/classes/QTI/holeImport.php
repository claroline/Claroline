<?php

/**
 * To import a question with holes in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class holeImport extends qtiImport
{
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
        }

        return $prompt;
    }
}
