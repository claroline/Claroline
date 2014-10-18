<?php

/**
 * To import a QCM question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class qcmImport extends qtiImport
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
        //$this->createQuestion();
        $this->createInteraction();
        //après avoir créer les objet Voir pour éventuellement s'appuyer sur le handler pour le persist
        //TODO resources liées
    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function getPrompt()
    {
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        $ci = $ib->getElementsByTagName("choiceInteraction")->item(0);
        $prompt = $ci->getElementsByTagName("prompt")->item(0)->nodeValue;

        return $prompt;
    }
}
