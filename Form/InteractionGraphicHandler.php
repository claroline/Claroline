<?php

namespace UJM\ExoBundle\Form;

use UJM\ExoBundle\Entity\Coords;

class InteractionGraphicHandler extends \UJM\ExoBundle\Form\InteractionHandler
{

    /**
     * Implements the abstract method
     *
     * @access public
     *
     * Return boolean
     */
    public function processAdd()
    {
        if ($this->request->getMethod() == 'POST') {
            $this->form->handleRequest($this->request);
            $data=$this->form->getData();
            //Uses the default category if no category selected
            $this->checkCategory($data);
            $this->checkTitle();
            if($this->validateNbClone() === FALSE) {
                return 'infoDuplicateQuestion';
            }

            if ($this->form->isValid()) {
                $this->onSuccessAdd($data);

                return true;
            }
        }

        return false;
    }

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     * @param \UJM\ExoBundle\Entity\InteractionGraphic $interGraph
     */
    protected function onSuccessAdd($interGraph)
    {
        $interGraph->getInteraction()->getQuestion()->setDateCreate(new \Datetime()); // Set Creation Date to today
        $interGraph->getInteraction()->getQuestion()->setUser($this->user); // add the user to the question
        $interGraph->getInteraction()->setType('InteractionGraphic'); // set the type of the question

        if ($this->request != NULL) {
            $width = $this->request->get('imagewidth'); // Get the width of the image
            $height = $this->request->get('imageheight'); // Get the height of the image

            $interGraph->setHeight($height);
            $interGraph->setWidth($width);

            $coords = $this->request->get('coordsZone'); // Get the answer zones

            $coord = preg_split('[,]', $coords); // Split all informations of one answer zones into a cell

            $lengthCoord = count($coord) - 1; // Number of answer zones

            $allCoords = $this->persitNewCoords($coord, $interGraph, $lengthCoord);
        } else {

            $allCoords = $interGraph->getCoords();

            $lengthCoord = count($allCoords);

        }

        $this->em->persist($interGraph);
        $this->em->persist($interGraph->getInteraction()->getQuestion());
        $this->em->persist($interGraph->getInteraction());

        for ($i = 0; $i < $lengthCoord; $i++) {
            $this->em->persist($allCoords[$i]);
        }

        $this->persistHints($interGraph);

        $this->em->flush();

        $this->addAnExercise($interGraph);

        $this->duplicateInter($interGraph);
    }

    /**
     * Implements the abstract method
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\InteractionGraphic $interGraph
     *
     * Return boolean
     */
    public function processUpdate($originalInterGraphic)
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

    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function onSuccessUpdate()
    {
        $arg_list = func_get_args();
        $interGraphic = $arg_list[0];
        $originalHints = $arg_list[1];

        $width = $this->request->get('imagewidth'); // Get the width of the image
        $height = $this->request->get('imageheight'); // Get the height of the image

        $interGraphic->setHeight($height);
        $interGraphic->setWidth($width);

        $coordsToDel = $this->em->getRepository('UJMExoBundle:Coords')->findBy(array('interactionGraphic' => $interGraphic->getId()));

        $coords = $this->request->get('coordsZone'); // Get the answer zones

        $coord = preg_split('[,]', $coords); // Split all informations of one answer zones into a cell

        $lengthCoord = count($coord) - 1; // Number of answer zones

        $allCoords = $this->persitNewCoords($coord, $interGraphic, $lengthCoord);

        $this->modifyHints($interGraphic, $originalHints);

        foreach ($coordsToDel as $ctd) {
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
     * @access private
     *
     * @param array $coord coords of good responses
     * @param \UJM\ExoBundle\Entity\InteractionGraphic $interGraph
     * @param integer $lengthCoord number of good coords
     *
     * @return array
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
     * @access private
     *
     * @param String route of response zone
     *
     * @return String shape of response zone
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
     * @access private
     *
     * @param String route of response zone
     *
     * @return String color of response zone
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
            case "k" :
                return "black";
            case "n" :
                return "brown";
            default :
                return "white";
        }
    }
}
