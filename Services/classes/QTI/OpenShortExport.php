<?php

/**
 * To export an open short response question in QTI.
 */
namespace UJM\ExoBundle\Services\classes\QTI;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UJM\ExoBundle\Entity\Question;

class OpenShortExport  extends OpenExport
{
    private $extendedTextInteraction;

    /**
     * overload the export method.
     *
     * @access public
     * @param Question $question
     * @param qtiRepository $qtiRepos
     * @return BinaryFileResponse
     */
    public function export(Question $question, qtiRepository $qtiRepos)
    {
        parent::export($question, $qtiRepos);
        $this->promptTag($this->itemBody);
        $this->mappingTag();
        $this->extendedTextInteractionTag();

        if (($this->interactionopen->getQuestion()->getFeedBack() != null)
                && ($this->interactionopen->getQuestion()->getFeedBack() != '')) {
            $this->qtiFeedBack($question->getFeedBack());
        }

        $this->document->save($this->qtiRepos->getUserDir().$this->question->getId().'_qestion_qti.xml');

        return $this->getResponse();
    }

    /**
     * add the tag mapping in responseDeclaration.
     */
    private function mappingTag()
    {
        $responseDeclaration = $this->responseDeclaration[$this->nbResponseDeclaration - 1];
        $correctResponse = $this->document->CreateElement('correctResponse');
        $responseDeclaration->appendChild($correctResponse);

        $responseDeclaration = $this->responseDeclaration[$this->nbResponseDeclaration - 1];
        $correctWordResponse = '';
        $mapping = $this->document->createElement('mapping');
        $mapping->setAttribute('defaultValue', '0');

        foreach ($this->interactionopen->getWordResponses() as $resp) {
            $i = 0;
            if ($correctWordResponse == '') {
                $correctWordResponse = $resp;
            } else {
                if ($correctWordResponse->getScore() < $resp->getScore()) {
                    $correctWordResponse = $resp;
                }
            }

            $mapEntry = $this->document->createElement('mapEntry');
            $mapEntry->setAttribute('mapKey', $resp->getResponse());
            $mapEntry->setAttribute('mappedValue', $resp->getScore());
            $mapEntry->setAttribute('caseSensitive', $resp->getCaseSensitive());
            $mapping->appendChild($mapEntry);
            
            if (($resp->getFeedback() != Null) && ($resp->getFeedback() != "")) {
                $feedbackInline = $this->document->CreateElement('feedbackInline');
                $feedbackInline->setAttribute("outcomeIdentifier", "FEEDBACK");
                $feedbackInline->setAttribute("identifier", "Choice" . $resp->getId());
                $feedbackInline->setAttribute("showHide", "show");
                $feedbackInlinetxt = $this->document->CreateTextNode($resp->getFeedback());
                $feedbackInline->appendChild($feedbackInlinetxt);
                $mapEntry->appendChild($feedbackInline);
            }

            ++$i;
        }
        $Tagvalue = $this->document->CreateElement('value');
        $responsevalue = $this->document->CreateTextNode($correctWordResponse->getResponse());
        $Tagvalue->appendChild($responsevalue);
        $correctResponse->appendChild($Tagvalue);
        $responseDeclaration->appendChild($correctResponse);

        $responseDeclaration->appendChild($mapping);
    }

    /**
     * add the tag extendedTextInteraction in itemBody.
     */
    private function extendedTextInteractionTag()
    {
        $this->extendedTextInteraction = $this->document->CreateElement('extendedTextInteraction');
        $this->extendedTextInteraction->setAttribute('responseIdentifier', 'RESPONSE');
        $this->itemBody->appendChild($this->extendedTextInteraction);
    }
}
