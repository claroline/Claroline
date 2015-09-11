<?php

/**
 * To export an open question in QTI.
 */
namespace UJM\ExoBundle\Services\classes\QTI;

class OpenExport extends QtiExport
{
    protected $interactionopen;

    /**
     * Implements the abstract method.
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param qtiRepository                     $qtiRepos
     */
    public function export(\UJM\ExoBundle\Entity\Interaction $interaction, qtiRepository $qtiRepos)
    {
        $this->qtiRepos = $qtiRepos;
        $this->question = $interaction->getQuestion();

        $this->interactionopen = $this->doctrine
                                ->getManager()
                                ->getRepository('UJMExoBundle:InteractionOpen')
                                ->findOneBy(array('interaction' => $interaction->getId()));

        $this->qtiHead('extendedText', $this->question->getTitle());
        $this->qtiResponseDeclaration('RESPONSE', 'string', $this->getCardinality());
        $this->qtiOutComeDeclaration();
        $this->defaultValueTag();
        $this->itemBodyTag();

        if (($this->interactionopen->getInteraction()->getFeedBack() != null)
                && ($this->interactionopen->getInteraction()->getFeedBack() != '')) {
            $this->qtiFeedBack($interaction->getFeedBack());
        }
    }

    /**
     * Implements the abstract method
     * add the tag prompt in extendedTextInteraction.
     */
    protected function promptTag()
    {
        $arg_list = func_get_args();
        $node = $arg_list[0];

        $prompt = $this->document->CreateElement('prompt');
        $prompttxt = $this->document->CreateTextNode($this->interactionopen->getInteraction()->getInvite());
        $prompt->appendChild($prompttxt);
        $node->appendChild($prompt);
    }

    /**
     * Implements the abstract method
     * add the tag correctResponse in responseDeclaration.
     */
    protected function correctResponseTag()
    {
        $this->correctResponse = $this->document->CreateElement('correctResponse');
        $this->responseDeclaration[0]->appendChild($this->correctResponse);
    }

    /**
     * add the tag defaultValue in outcomeDeclaration.
     */
    protected function defaultValueTag()
    {
        $defaultValue = $this->document->createElement('defaultValue');
        $Tagvalue = $this->document->CreateElement('value');
        $responsevalue = $this->document->CreateTextNode($this->interactionopen->getScoreMaxLongResp());
        $Tagvalue->appendChild($responsevalue);
        $defaultValue->appendChild($Tagvalue);
        $this->outcomeDeclaration->appendChild($defaultValue);
    }

    /**
     * add the tag defaultValue in outcomeDeclaration.
     *
     *
     * @return String value of cardinality for the responseDeclaration element
     */
    private function getCardinality()
    {
        $cardinality = 'single';
        if ($this->interactionopen->getTypeOpenQuestion() == 'short') {
            $cardinality = 'multiple';
        }

        return $cardinality;
    }
}
