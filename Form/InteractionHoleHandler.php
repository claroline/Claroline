<?php

/**
 * ExoOnLine
 * Copyright or © or Copr. Université Jean Monnet (France), 2012
 * dsi.dev@univ-st-etienne.fr
 *
 * This software is a computer program whose purpose is to [describe
 * functionalities and technical features of your software].
 *
 * This software is governed by the CeCILL license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
*/

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use Claroline\CoreBundle\Entity\User;

use UJM\ExoBundle\Entity\InteractionHole;
use UJM\ExoBundle\Entity\WordResponse;

class InteractionHoleHandler {
    protected $form;
    protected $request;
    protected $em;
    protected $user;
    protected $exercise;
    protected $validator;

    public function __construct(Form $form, Request $request, EntityManager $em, User $user, $validator, $exercise=-1)
    {
        $this->form      = $form;
        $this->request   = $request;
        $this->em        = $em;
        $this->user      = $user;
        $this->exercise  = $exercise;
        $this->validator = $validator;
    }

     public function processAdd()
    {
        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                foreach ($this->form->getData()->getHoles() as $h) {
                    foreach ($h->getWordResponses() as $wr) {
                        $errorList = $this->validator->validate($wr);
                        if (count($errorList) > 0) {
                            //echo 'test : '.$errorList[0]->getMessage();die();
                            return $errorList[0]->getMessage();
                        }
                    }
                }
                $this->onSuccessAdd($this->form->getData());

                return true;
            }
        }

        return false;
    }

    private function onSuccessAdd(InteractionHole $interHole)
    {
        // to avoid bug with code tinymce
        $htmlTiny = $interHole->getHtml();
        $interHole->getInteraction()->getQuestion()->setDateCreate(new \Datetime());
        $interHole->getInteraction()->getQuestion()->setUser($this->user);
        $interHole->getInteraction()->setType('InteractionHole');

        foreach ($interHole->getHoles() as $hole) {
            foreach ($hole->getWordResponses() as $wr) {
                //$hole->addWordResponse($wr);
                $wr->setHole($hole);
                $this->em->persist($wr);
            }
            $interHole->addHole($hole);
            $this->em->persist($hole);
        }
        $interHole->setHtml($htmlTiny);
        $this->em->persist($interHole);
        $this->em->persist($interHole->getInteraction()->getQuestion());
        $this->em->persist($interHole->getInteraction());

        foreach ($interHole->getInteraction()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            $interHole->getInteraction()->addHint($hint);
            $this->em->persist($hint);
        }

        $this->em->flush();
        
        $this->htmlWithoutValue($interHole);
    }

    public function processUpdate(InteractionHole $originalInterHole)
    {
        $originalHoles = array();
        $originalHints = array();

        // Create an array of the current Choice objects in the database
        foreach ($originalInterHole->getHoles() as $hole) {
            $originalHoles[] = $hole;
        }
        foreach ($originalInterHole->getInteraction()->getHints() as $hint) {
            $originalHints[] = $hint;
        }

        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                foreach ($this->form->getData()->getHoles() as $h) {
                    foreach ($h->getWordResponses() as $wr) {
                        $errorList = $this->validator->validate($wr);
                        if (count($errorList) > 0) {
                            //echo 'test : '.$errorList[0]->getMessage();die();
                            return $errorList[0]->getMessage();
                        }
                    }
                }
                $this->onSuccessUpdate($this->form->getData(), $originalHoles, $originalHints);

                return true;
            }
        }

        return false;
    }

    private function onSuccessUpdate(InteractionHole $interHole, $originalHoles, $originalHints)
    {
        // to avoid bug with code tinymce
        $htmlTiny = $interHole->getHtml();

        // filter $originalHoles to contain hole no longer present
        foreach ($interHole->getHoles() as $hole) {

            //to remove key word not yet used
            $this->delKeyWord($hole, $originalHoles);

            foreach ($originalHoles as $key => $toDel) {
                if ($toDel->getId() == $hole->getId()) {
                    unset($originalHoles[$key]);
                }
            }
        }

        // remove the relationship between the hole and the interactionhole
        foreach ($originalHoles as $hole) {
            // remove the hole from the interactionhole
            $interHole->getHoles()->removeElement($hole);

            // if you wanted to delete the Hole entirely, you can also do that
            $this->em->remove($hole);
        }

        // filter $originalHints to contain hint no longer present
        foreach ($interHole->getInteraction()->getHints() as $hint) {
            foreach ($originalHints as $key => $toDel) {
                if ($toDel->getId() == $hint->getId()) {
                    unset($originalHints[$key]);
                }
            }
        }

        // remove the relationship between the hint and the interactionhole
        foreach ($originalHints as $hint) {
            // remove the Hint from the interactionhole
            $interHole->getInteraction()->getHints()->removeElement($hint);

            // if you wanted to delete the Hint entirely, you can also do that
            $this->em->remove($hint);
        }

        $interHole->setHtml($htmlTiny);
        $this->em->persist($interHole);
        $this->em->persist($interHole->getInteraction()->getQuestion());
        $this->em->persist($interHole->getInteraction());

        // On persiste tous les holes de l'interaction hole.
        foreach ($interHole->getHoles() as $hole) {
            foreach ($hole->getWordResponses() as $wr) {
                //$hole->addWordResponse($wr);
                $wr->setHole($hole);
                $this->em->persist($wr);
            }
            $interHole->addHole($hole);
            $this->em->persist($hole);
        }

        //On persite tous les hints de l'entité interaction
        foreach ($interHole->getInteraction()->getHints() as $hint) {
            $interHole->getInteraction()->addHint($hint);
            $this->em->persist($hint);
        }

        $this->em->flush();
        
        $this->htmlWithoutValue($interHole);
    }

    private function delKeyWord($hole, $originalHoles)
    {
        $wordResponses = $hole->getWordResponses()->toArray();

        foreach($originalHoles as $holeOrig)
        {
            $originalWords = $holeOrig->getwordResponses()->getSnapshot();
            if($hole->getId() === $holeOrig->getId())
            {
                foreach($wordResponses as $word)
                {
                    foreach($originalWords as $key => $toDel)
                    {
                        if ($toDel->getId() === $word->getId())
                        {
                            unset($originalWords[$key]);
                        }
                    }
                }

                // remove the relationship between the hole and the interactionhole
                foreach ($originalWords as $word)
                {
                    // remove the wr from the wordResponse
                    $hole->getWordResponses()->removeElement($word);

                    // if you wanted to delete the Hole entirely, you can also do that
                    $this->em->remove($word);
                }

            }
        }
    }

    private function htmlWithoutValue($interHole)
    {
        //id hole in html = $hole->getPosition()
        $html = $interHole->getHtml();
        $tabInputValue = explode('value="', $html);
        $tabHoles = array();

        foreach($interHole->getHoles() as $hole)
        {
            if ($hole->getSelector() === false) {
                $tabHoles[$hole->getPosition()] = $hole;
            } else {
                $selectInHtml = explode('<select id="' . $hole->getPosition() . '" class="blank">', $html);
                $selectInHtml = explode ('</select>', $selectInHtml[1]);
                $html = str_replace($selectInHtml[0], '', $html);

                $pos = $hole->getPosition();
                $regExpr = "<select id=\"$pos\" class=\"blank\">";
                $select = "<select id=\"$pos\" class=\"blank\">";

                $wrs = array();
                foreach ($hole->getWordResponses() as $wr) {
                    $wrs[] = $wr;
                }
                shuffle($wrs);

                foreach ($wrs as $wr) {
                    $id = $wr->getId();
                    $response = $wr->getResponse();
                    $select .= "<option value=\"$id\">$response</option>";
                }
                $select .= '</select>';
                $html = str_replace($regExpr, $select, $html);
            }
        }
        ksort($tabHoles);
        $tabHoles = array_values($tabHoles);

        for( $i= 0; $i < count($tabInputValue); $i++)
        {
            $inputValue = explode('"', $tabInputValue[$i]);
            $regExpr = 'value="'.$inputValue[0].'"';
            $html = str_replace($regExpr, 'value=""', $html);
        }
        $interHole->setHtmlWithoutValue($html);
        $this->em->persist($interHole);
        $this->em->flush();

    }
}

?>
