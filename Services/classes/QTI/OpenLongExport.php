<?php

/**
 * To export an open long response question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Question;

class OpenLongExport extends OpenExport
{
    private $extendedTextInteraction;

    /**
     * overload the export method
     *
     * @access public
     * @param Question $question
     * @param qtiRepository $qtiRepos
     * @return \UJM\ExoBundle\Services\classes\QTI\BinaryFileResponse|void
     */
    public function export(Question $question, qtiRepository $qtiRepos)
    {
        parent::export($question, $qtiRepos);
        $this->extendedTextInteractionTag();
        $this->promptTag($this->extendedTextInteraction);

        $this->document->save($this->qtiRepos->getUserDir().$this->question->getId().'_qestion_qti.xml');

        return $this->getResponse();
    }

    /**
     * add the tag extendedTextInteraction in itemBody
     *
     * @access private
     *
     */
    private function extendedTextInteractionTag()
    {
        $this->extendedTextInteraction = $this->document->CreateElement('extendedTextInteraction');
        $this->extendedTextInteraction->setAttribute("responseIdentifier", "RESPONSE");
        $this->itemBody->appendChild($this->extendedTextInteraction);
    }
}
