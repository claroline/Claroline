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
        $this->genericInteraction();
        //Voir pour éventuellement s'appuyer sur le handler
    }
}
