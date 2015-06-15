<?php

/**
 * To import a long open question
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class OpenLongImport extends OpenImport
{
    /**
     * overload the export method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     * @param DOMElement $assessmentItem assessmentItem of the question to imported
     *
     * @return UJM\ExoBundle\Entity\InteractionOpen
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem)
    {
        $this->codeType = 2;
        parent::import($qtiRepos, $assessmentItem);

        return $this->interactionOpen;
    }

    /**
     *
     * @access protected
     *
     */
    protected function getPromptChild()
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
