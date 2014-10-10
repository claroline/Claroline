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
    public function import(qtiRepository $qtiRepos)
    {
        $this->qtiRepos = $qtiRepos;
        echo 'qcm import';die();
    }
}
