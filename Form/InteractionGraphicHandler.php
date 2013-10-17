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

use UJM\ExoBundle\Entity\InteractionGraphic;
use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Exercise;


class InteractionGraphicHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $user;
    protected $exercise;

    public function __construct(Form $form, Request $request, EntityManager $em, User $user, $exercise = -1)
    {
        $this->form     = $form;
        $this->request  = $request;
        $this->em       = $em;
        $this->user     = $user;
        $this->exercise = $exercise;
    }

    public function processAdd()
    {
        if ($this->request->getMethod() == 'POST') {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccessAdd($this->form->getData());

                return true;
            }
        }

        return false;
    }

    private function onSuccessAdd(InteractionGraphic $interGraph)
    {
        $interGraph->getInteraction()->getQuestion()->setDateCreate(new \Datetime()); // Set Creation Date to today
        $interGraph->getInteraction()->getQuestion()->setUser($this->user); // add the user to the question
        $interGraph->getInteraction()->setType('InteractionGraphic'); // set the type of the question

        $width = $this->request->get('imgwidth'); // Get the width of the image
        $height = $this->request->get('imgheight'); // Get the height of the image

        $interGraph->setHeight($height);
        $interGraph->setWidth($width);

        $coords = $this->request->get('coordsZone'); // Get the answer zones

        $coord = preg_split('[,]', $coords); // Split all informations of one answer zones into a cell

        $lengthCoord = count($coord) - 1; // Number of answer zones

        $allCoords = $this->persitNewCoords($coord, $interGraph, $lengthCoord);

        $this->em->persist($interGraph);
        $this->em->persist($interGraph->getInteraction()->getQuestion());
        $this->em->persist($interGraph->getInteraction());

        for ($i = 0; $i < $lengthCoord; $i++) {
            $this->em->persist($allCoords[$i]);
        }
        $this->em->flush();
    }

    public function processUpdate(InteractionGraphic $originalInterGraphic)
    {
        $originalHints = array();

        foreach ($originalInterGraphic->getInteraction()->getHints() as $hint) {
            $originalHints[] = $hint;
        }

        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                $this->onSuccessUpdate($this->form->getData(), $originalHints);

                return true;
            }
        }

        return false;
    }

    private function onSuccessUpdate(InteractionGraphic $interGraphic, $originalHints)
    {
        $width = $this->request->get('imgwidth'); // Get the width of the image
        $height = $this->request->get('imgheight'); // Get the height of the image

        $interGraphic->setHeight($height);
        $interGraphic->setWidth($width);

        $coordsToDel = $this->em->getRepository('UJMExoBundle:Coords')->findBy(array('interactionGraphic' => $interGraphic->getId()));

        $coords = $this->request->get('coordsZone'); // Get the answer zones

        $coord = preg_split('[,]', $coords); // Split all informations of one answer zones into a cell

        $lengthCoord = count($coord) - 1; // Number of answer zones

        $allCoords = $this->persitNewCoords($coord, $interGraphic, $lengthCoord);

        foreach ($interGraphic->getInteraction()->getHints() as $hint) {
            foreach ($originalHints as $key => $toDel) {
                if ($toDel->getId() == $hint->getId()) {
                    unset($originalHints[$key]);
                }
            }
        }

        // remove the relationship between the hint and the interactionGraphic
        foreach ($originalHints as $hint) {
            // remove the Hint from the interactionGraphic
            $interGraphic->getInteraction()->getHints()->removeElement($hint);

            // if you wanted to delete the Hint entirely, you can also do that
            $this->em->remove($hint);
        }

        //Persit all the  hints of entity Interaction
        foreach ($interGraphic->getInteraction()->getHints() as $hint) {
            $interGraphic->getInteraction()->addHint($hint);
            $this->em->persist($hint);
        }

        foreach ($coordsToDel as $ctd) {
            // if you wanted to delete the Hint entirely, you can also do that
            $this->em->remove($ctd);
        }

        for ($i = 0; $i < $lengthCoord; $i++) {
            $this->em->persist($allCoords[$i]);
        }

        $this->em->persist($interGraphic);
        $this->em->flush();

    }

        /**
     * Persist coordonates of the answer zones into the database.
     *
     */
    private function persitNewCoords($coord, $interGraph, $lengthCoord)
    {
        $result = array();
        for ($i = 0; $i < $lengthCoord; $i++) {

            $inter = preg_split('[;]', $coord[$i]); // Divide the src of the answer zone and the other informations

            $before = array("-","~");
            $after = array(",",",");

            $data = str_replace($before, $after, $inter[1]); // replace separation punctuation of the informations ...

            list(${'value'.$i}, ${'point'.$i}, ${'size'.$i}) = explode(",", $data); //... in order to split informations

            ${'point'.$i} = str_replace('/', '.', ${'point'.$i}); // set the score to a correct value

            // And persist it into the Database
            ${'url'.$i} = $inter[0];

            ${'value'.$i} = str_replace("_", ",", ${'value'.$i});
            ${'url'.$i} = substr(${'url'.$i}, strrpos(${'url'.$i}, '/bundles'));

            ${'shape'.$i} = $this->getShape(${'url'.$i});
            ${'color'.$i} = $this->getColor(${'url'.$i});

            ${'co'.$i} = new Coords();

            ${'co'.$i}->setValue(${'value'.$i});
            ${'co'.$i}->setShape(${'shape'.$i});
            ${'co'.$i}->setColor(${'color'.$i});
            ${'co'.$i}->setScoreCoords(${'point'.$i});
            ${'co'.$i}->setInteractionGraphic($interGraph);
            ${'co'.$i}->setSize(${'size'.$i});

            $result[$i] = ${'co'.$i};
        }

        return $result;
    }

        /**
     * Get the shape of the answer zone
     *
     */
    private function getShape($url)
    {
        // Recover the shape of an answer zone thanks to its src
        $temp = strrpos($url, 'graphic/') + 8;
        $chain = substr($url, $temp, 1);

        if ($chain == "s") {
            return "square";
        } else if ($chain == "c") {
            return "circle";
        }
    }

    /**
     * Get the color of the answer zone
     *
     */
    private function getColor($url)
    {
        // Recover the color of an answer zone thanks to its src
        $temp = strrpos($url, '.') - 1;
        $chain = substr($url, $temp, 1);

        switch ($chain) {
            case "w" :
                return "white";
            case "g" :
                return "green";
            case "p" :
                return "purple";
            case "b" :
                return "blue";
            case "r" :
                return "red";
            case "o" :
                return "orange";
            case "y" :
                return "yellow";
            default :
                return "white";
        }
    }
}