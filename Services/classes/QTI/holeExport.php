<?php

/**
 * To export question with holes in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

class holeExport extends qtiExport
{
    private $interactionhole;
    private $correctResponse = array();

    /**
     * Implements the abstract method
     *
     * @access public
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     * @param qtiRepository $qtiRepos
     *
     */
    public function export(\UJM\ExoBundle\Entity\Interaction $interaction, qtiRepository $qtiRepos)
    {
        $this->qtiRepos = $qtiRepos;
        $this->question = $interaction->getQuestion();

        $this->interactionhole = $this->doctrine
                                ->getManager()
                                ->getRepository('UJMExoBundle:InteractionHole')
                                ->findOneBy(array('interaction' => $interaction->getId()));

        $this->qtiHead('textEntry', $this->question->getTitle());
        foreach($this->interactionhole->getHoles() as $hole) {
            $this->qtiResponseDeclaration('blank_'.$this->nbResponseDeclaration, 'string', 'single');
            $this->correctResponseTag();
            $this->mappingTag($hole);
        }
        $this->qtiOutComeDeclaration();

        $this->itemBodyTag();
        $this->promptTag();
        $this->textWithHole();

        if(($this->interactionhole->getInteraction()->getFeedBack()!=Null)
                && ($this->interactionhole->getInteraction()->getFeedBack()!="") ){
            $this->qtiFeedBack($interaction->getFeedBack());
        }

        $this->document->save($this->qtiRepos->getUserDir().'testfile.xml');

        return $this->getResponse();
    }

    /**
     * Implements the abstract method
     * add the tag correctResponse in responseDeclaration
     *
     * @access protected
     *
     */
    protected function correctResponseTag()
    {
        $responseDeclaration = $this->responseDeclaration[$this->nbResponseDeclaration - 1];
        $this->correctResponse[$this->nbResponseDeclaration - 1] = $this->document->CreateElement('correctResponse');
        $responseDeclaration->appendChild($this->correctResponse[$this->nbResponseDeclaration - 1]);
    }

    /**
     * add the tag mapping in responseDeclaration
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Hole $hole
     *
     */
    private  function mappingTag($hole)
    {
        $responseDeclaration = $this->responseDeclaration[$this->nbResponseDeclaration - 1];
        $correctResponse = $this->correctResponse[$this->nbResponseDeclaration - 1];
        $correctWordResponse = '';
        $mapping = $this->document->createElement("mapping");
        $mapping->setAttribute("defaultValue", "0");

        foreach ($hole->getWordResponses() as $resp) {
            if ($correctWordResponse == '') {
                $correctWordResponse = $resp;
            } else {
                if ($correctWordResponse->getScore() < $resp->getScore()) {
                    $correctWordResponse = $resp;
                }
            }

            $mapEntry =  $this->document->createElement("mapEntry");
            $mapEntry->setAttribute("mapKey", $resp->getResponse());
            $mapEntry->setAttribute("mappedValue",$resp->getScore());
            $mapping->appendChild($mapEntry);
        }
        $Tagvalue = $this->document->CreateElement("value");
        $responsevalue =  $this->document->CreateTextNode($correctWordResponse->getResponse());
        $Tagvalue->appendChild($responsevalue);
        $correctResponse->appendChild($Tagvalue);
        $responseDeclaration->appendChild($correctResponse);

        $responseDeclaration->appendChild($mapping);
    }

    /**
     * Implements the abstract method
     * add the tag prompt in itemBody
     *
     * @access protected
     *
     */
    protected function promptTag()
    {
        $prompt = $this->document->CreateElement('prompt');
        $prompttxt = $this->document->CreateTextNode($this->interactionhole->getInteraction()->getInvite());
        $prompt->appendChild($prompttxt);
        $this->itemBody->appendChild($prompt);
    }

    /**
     * Text with hole
     *
     * @access private
     *
     */
    private function textWithHole()
    {
        $textEntryInteraction = '';
        $newId = 1;
        $html = htmlspecialchars_decode($this->interactionhole->getHtmlWithoutValue());
        $regexOpt = '(<option\\s+value="\d+">\w+</option>)';
        $html = preg_replace($regexOpt, '', $html);

        $regex = '(<input\\s+id="\d+"\\s+class="blank"\\s+name="blank_\d+"\\s+size="\d+"\\s+type="text"\\s+value=""\\s+\/>|<select\\s+id="\d+"\\s+class="blank"\\s+name="blank_\d+"></select>)';
        preg_match_all($regex, $html, $matches);
        foreach ($matches[0] as $matche) {
            $tabMatche = explode('"', $matche);
            $id = $tabMatche[1];
            $name = $tabMatche[5];
            if (substr($matche, 1, 5) == 'input') {
                $size = $tabMatche[7];
                $textEntryInteraction = str_replace('input', 'textEntryInteraction', $matche);
                $textEntryInteraction = str_replace('class="blank" ', '', $textEntryInteraction);
                $textEntryInteraction = str_replace('type="text" ', '', $textEntryInteraction);
                $textEntryInteraction = str_replace('value="" ', '', $textEntryInteraction);
                $textEntryInteraction = str_replace('id="'.$id.'"', 'responseIdentifier="blank_'.$newId.'"', $textEntryInteraction);
                $textEntryInteraction = str_replace('name="'.$name.'"', '', $textEntryInteraction);
                $textEntryInteraction = str_replace('size="'.$size.'"', 'expectedLength="'.$size.'"', $textEntryInteraction);
            } else {
                $textEntryInteraction = str_replace('</select>', '', $matche);
                $textEntryInteraction = str_replace('select', 'textEntryInteraction', $textEntryInteraction);
                $textEntryInteraction = str_replace('id="'.$id.'"', 'responseIdentifier="blank_'.$newId.'"', $textEntryInteraction);
                $textEntryInteraction = str_replace('class="blank" ', 'expectedLength="'.$size.'"', $textEntryInteraction);
                $textEntryInteraction = str_replace('name="'.$name.'"', ' /', $textEntryInteraction);
            }
            $html = str_replace($matche, $textEntryInteraction, $html);
            $newId++;
        }
        $fragment = $this->document->createDocumentFragment();
        $fragment->appendXML($html);
        $this->itemBody->appendChild($fragment);
    }
}