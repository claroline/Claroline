<?php

/**
 * To import a question with holes in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Hole;
use UJM\ExoBundle\Entity\InteractionHole;
use UJM\ExoBundle\Entity\WordResponse;

class holeImport extends qtiImport
{
    protected $interactionHole;
    protected $qtiTextWithHoles;
    protected $textHtml;
    protected $tabWrOpt = array();

    /**
     * Implements the abstract method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     * @param DOMElement $assessmentItem assessmentItem of the question to imported
     *
     * @return UJM\ExoBundle\Entity\InteractionHole
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem)
    {
        $this->qtiRepos = $qtiRepos;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);

        $this->createQuestion();

        $this->createInteraction();
        $this->interaction->setType('InteractionHole');
        $this->doctrine->getManager()->persist($this->interaction);

        $this->createInteractionHole();

        $this->doctrine->getManager()->flush();

        $this->addOptionValue();

        return $this->interactionHole;
    }

    /**
     * Create the InteractionHole object
     *
     * @access protected
     *
     */
    protected function createInteractionHole()
    {
        $this->interactionHole = new InteractionHole();
        $this->interactionHole->setInteraction($this->interaction);

        $this->getQtiTextWithHoles();
        $this->getHtml();
        $this->getHtmlWithoutValue();

        $this->doctrine->getManager()->persist($this->interactionHole);
    }

    /**
     * Get property html
     *
     * @access protected
     *
     */
    protected function getHtml()
    {
        $this->textHtml = $this->qtiTextWithHoles;
        $newId = 1;
        $regex = '(<textEntryInteraction.*?>|<inlineChoiceInteraction.*?</inlineChoiceInteraction>)';
        preg_match_all($regex, $this->qtiTextWithHoles, $matches);
        foreach ($matches[0] as $matche) {
            $tabMatche = explode('"', $matche);
            $responseIdentifier = $tabMatche[1];
            $correctResponse    = $this->getCorrectResponse($responseIdentifier);
            if (substr($matche, 1, 20) == 'textEntryInteraction') {
                $expectedLength = $tabMatche[3];
                $text = str_replace('textEntryInteraction', 'input', $matche);
                /*For old questions with holes */
                $text = preg_replace('(name=".*?")', '', $text);
                if (isset($tabMatche[5])) {
                    $text = str_replace('size="'.$tabMatche[5].'"', 'size="'.$tabMatche[5].'" type="text" value="'.$correctResponse.'"', $text);
                }
                /******************************/
                $text = str_replace('responseIdentifier="'.$responseIdentifier.'"', 'id="'.$newId.'" class="blank" autocomplete="off" name="blank_'.$newId.'"', $text);
                $text = str_replace('expectedLength="'.$expectedLength.'"', 'size="'.$expectedLength.'" type="text" value="'.$correctResponse.'"', $text);
                $this->createHole($expectedLength, $responseIdentifier, false, $newId);
            } else {
               $text = str_replace('inlineChoiceInteraction', 'select', $matche);
               $text = str_replace('responseIdentifier="'.$responseIdentifier.'"', 'id="'.$newId.'" class="blank" name="blank_'.$newId.'"', $text);
               $text = str_replace('inlineChoice', 'option', $text);
               $regexOpt = '(<option identifier=.*?>)';
               preg_match_all($regexOpt, $text, $matchesOpt);
               foreach ($matchesOpt[0] as $matcheOpt) {
                   $tabMatcheOpt = explode('"', $matcheOpt);
                   $holeID       = $tabMatcheOpt[1];
                   if ($correctResponse == $holeID) {
                       $opt = preg_replace('(\s*identifier="'.$holeID.'")', ' holeCorrectResponse="1"', $matcheOpt);
                   } else {
                       $opt = preg_replace('(\s*identifier="'.$holeID.'")', ' holeCorrectResponse="0"', $matcheOpt);
                   }
                   $text = str_replace($matcheOpt, $opt, $text);
               }
               $this->createHole(15, $responseIdentifier, true, $newId);
            }
            $newId++;
            $this->textHtml = str_replace($matche, $text, $this->textHtml);
            $textHtmlClean = preg_replace('(<option holeCorrectResponse="0".*?</option>)', '', $this->textHtml);
            $textHtmlClean = str_replace(' holeCorrectResponse="1"', '', $textHtmlClean);
        }
        $this->interactionHole->setHtml($textHtmlClean);
    }

    /**
     * Get correctResponse
     *
     * @access protected
     *
     * @param String $identifier identifier of hole
     *
     */
    protected function getCorrectResponse($identifier)
    {
        $correctResponse = '';
        foreach($this->assessmentItem->getElementsByTagName("responseDeclaration") as $rp) {
            if ($rp->getAttribute("identifier") == $identifier) {
                $correctResponse = $rp->getElementsByTagName("correctResponse")
                                      ->item(0)->getElementsByTagName("value")
                                      ->item(0)->nodeValue;
            }
        }

        return $correctResponse;
    }

    /**
     * Get property htmlWithoutValue
     *
     * @access protected
     *
     */
    protected function getHtmlWithoutValue()
    {
        $htmlWithoutValue = $this->textHtml;
        $regex = '(<input.*?class="blank".*?>)';
        preg_match_all($regex, $htmlWithoutValue, $matches);
        foreach ($matches[0] as $matche) {
            if (substr($matche, 1, 5) == 'input') {
                $tabMatche = explode('"', $matche);
                $value = $tabMatche[13];
                $inputWithoutValue = str_replace('value="'.$value.'"', 'value=""', $matche);
                $htmlWithoutValue = str_replace($matche, $inputWithoutValue, $htmlWithoutValue);
            }
        }
        $this->interactionHole->sethtmlWithoutValue($htmlWithoutValue);
    }

    /**
     * addOptionValue : to add the id of wordreponse object as a value for the option element
     *
     * @access protected
     *
     */
    protected function addOptionValue()
    {
        $numOpt = 0;
        $htmlWithoutValue = $this->interactionHole->getHtmlWithoutValue();
        $regex = '(<select.*?class="blank".*?</select>)';
        preg_match_all($regex, $htmlWithoutValue, $selects);
        foreach ($selects[0] as $select) {
            $newSelect = $select;
            $regexOpt = '(<option.*?</option>)';
            preg_match_all($regexOpt, $select, $options);
            foreach ($options[0] as $option) {
                $domOpt = new \DOMDocument();
                $domOpt->loadXML($option);
                $opt = $domOpt->getElementsByTagName('option')->item(0);
                $opt->removeAttribute('holeCorrectResponse');
                $wr = $this->tabWrOpt[$numOpt];
                $optVal = $domOpt->createAttribute('value');
                $optVal->value = $wr->getId();
                $opt->appendChild($optVal);
                $newSelect = str_replace($option, $domOpt->saveHTML(), $newSelect);
                $numOpt++;
            }
            $htmlWithoutValue = str_replace($select, $newSelect,  $htmlWithoutValue);
        }
        $this->interactionHole->setHtmlWithoutValue($htmlWithoutValue);
        $this->doctrine->getManager()->persist($this->interactionHole);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Create hole
     *
     * @access protected
     *
     * @param Intger $size hole's size for the input
     * @param String $qtiId id of hole in the qti file
     * @param boolean $selector text or list
     * @param Integer $position position of hole in the text
     *
     */
    protected function createHole($size, $qtiId, $selector, $position)
    {
        $hole = new Hole();
        $hole->setSize($size);
        $hole->setSelector($selector);
        $hole->setPosition($position);
        $hole->setInteractionHole($this->interactionHole);

        $this->doctrine->getManager()->persist($hole);

        $this->createWordResponse($qtiId, $hole);
    }

    /**
     * Create wordResponse
     *
     * @access protected
     *
     * @param String $qtiId id of hole in the qti file
     * @param UJM\ExoBundle\Entity\Hole $hole
     *
     */
    protected function createWordResponse($qtiId, $hole)
    {
        foreach($this->assessmentItem->getElementsByTagName("responseDeclaration") as $rp) {
            if ($rp->getAttribute("identifier") == $qtiId) {
                $mapping = $rp->getElementsByTagName("mapping")->item(0);
                if ($hole->getSelector() === false) {
                    $this->wordResponseForSimpleHole($mapping, $hole);
                } else {
                    $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
                    $this->wordResponseForList($qtiId, $ib, $mapping, $hole);
                }
            }
        }
    }

    /**
     * Create wordResponseForSimpleHole
     *
     * @access protected
     *
     * @param DOMNodelist::item $mapping element mapping
     * @param UJM\ExoBundle\Entity\Hole $hole
     *
     */
    protected function wordResponseForSimpleHole($mapping, $hole)
    {
        foreach ($mapping->getElementsByTagName("mapEntry") as $mapEntry) {
            $keyWord = new WordResponse();
            $keyWord->setResponse($mapEntry->getAttribute('mapKey'));
            $keyWord->setScore($mapEntry->getAttribute('mappedValue'));
            $keyWord->setHole($hole);
            if ($mapEntry->getAttribute('caseSensitive') == true) {
                $keyWord->setCaseSensitive(true);
            } else {
                $keyWord->setCaseSensitive(false);
            }
            $this->doctrine->getManager()->persist($keyWord);
        }
    }

    /**
     * Create wordResponseForList
     *
     * @access protected
     *
     * @param String $qtiId id of hole in the qti file
     * @param DOMNodelist::item $ib element itemBody
     * @param DOMNodelist::item $mapping element mapping
     * @param UJM\ExoBundle\Entity\Hole $hole
     *
     */
    protected function wordResponseForList($qtiId, $ib, $mapping, $hole)
    {
        foreach ($ib->getElementsByTagName("inlineChoiceInteraction") as $ici) {
            if ($ici->getAttribute('responseIdentifier') == $qtiId) {
                foreach ($ici->getElementsByTagName('inlineChoice') as $ic) {
                    $keyWord = new WordResponse();
                    $score = 0;
                    $matchScore = false;
                    $keyWord->setResponse($ic->nodeValue);
                    foreach ($mapping->getElementsByTagName("mapEntry") as $mapEntry) {
                        if ($mapEntry->getAttribute('mapKey') == $ic->getAttribute('identifier')) {
                            $score = $mapEntry->getAttribute('mappedValue');
                            $matchScore = true;
                        }
                        if ($mapEntry->getAttribute('caseSensitive') == true) {
                            $keyWord->setCaseSensitive(true);
                        } else {
                            $keyWord->setCaseSensitive(false);
                        }
                    }
                    if ($matchScore === false) {
                        foreach ($mapping->getElementsByTagName("mapEntry") as $mapEntry) {
                            if ($mapEntry->getAttribute('mapKey') == $ic->nodeValue) {
                                $score = $mapEntry->getAttribute('mappedValue');
                            }
                        }
                    }
                    $keyWord->setScore($score);
                    $keyWord->setHole($hole);
                    $this->doctrine->getManager()->persist($keyWord);
                    $this->tabWrOpt[] = $keyWord;
                }
            }
        }
    }

    /**
     * Get qtiTextWithHoles
     *
     * @access protected
     *
     */
    protected function getQtiTextWithHoles()
    {
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        $text = $this->domElementToString($ib);
        $text = str_replace('<itemBody>', '', $text);
        $text = str_replace('</itemBody>', '', $text);
        $this->qtiTextWithHoles = html_entity_decode($text);
    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function getPrompt()
    {
        $text = '';
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        if ($ib->getElementsByTagName("prompt")->item(0)) {
            $prompt = $ib->getElementsByTagName("prompt")->item(0);
            $text = $this->domElementToString($prompt);
            $text = str_replace('<prompt>', '', $text);
            $text = str_replace('</prompt>', '', $text);
            $text = html_entity_decode($text);
            $ib->removeChild($ib->getElementsByTagName("prompt")->item(0));
        }

        return $text;
    }
}
