<?php

/**
 * To export an open short response question in QTI.
 */
namespace UJM\ExoBundle\Services\classes\QTI;

class OpenShortExport  extends OpenExport
{
    private $extendedTextInteraction;

    /**
     * overload the export method.
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param qtiRepository                     $qtiRepos
     */
    public function export(\UJM\ExoBundle\Entity\Interaction $interaction, qtiRepository $qtiRepos)
    {
        parent::export($interaction, $qtiRepos);
        $this->promptTag($this->itemBody);
        $this->mappingTag();
        $this->extendedTextInteractionTag();

        if (($this->interactionopen->getInteraction()->getFeedBack() != null)
                && ($this->interactionopen->getInteraction()->getFeedBack() != '')) {
            $this->qtiFeedBack($interaction->getFeedBack());
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
