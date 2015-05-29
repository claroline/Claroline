<?php

namespace UJM\ExoBundle\Form;

class InteractionOpenHandler extends \UJM\ExoBundle\Form\InteractionHandler
{

     /**
     * Implements the abstract method
     *
     * @access public
     *
     */
    public function processAdd()
    {
        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);
            //Uses the default category if no category selected
            $this->checkCategory();
            $this->checkTitle();
            if($this->validateNbClone() === FALSE) {
                    return 'infoDuplicateQuestion';
            }
            if ( $this->form->isValid() ) {
                    $this->onSuccessAdd($this->form->getData());
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
     * @param \UJM\ExoBundle\Entity\InteractionOpen $interOpen
     */
    protected function onSuccessAdd($interOpen)
    {
        $interOpen->getInteraction()->getQuestion()->setDateCreate(new \Datetime());
        $interOpen->getInteraction()->getQuestion()->setUser($this->user);
        $interOpen->getInteraction()->setType('InteractionOpen');

        $this->em->persist($interOpen);
        $this->em->persist($interOpen->getInteraction()->getQuestion());
        $this->em->persist($interOpen->getInteraction());

        foreach ($interOpen->getWordResponses() as $wr) {
            $wr->setInteractionOpen($interOpen);
            $this->em->persist($wr);
        }

        $this->persistHints($interOpen);

        $this->em->flush();

        $this->addAnExercise($interOpen);

        $this->duplicateInter($interOpen);

    }

    /**
     * Implements the abstract method
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\InteractionOpen $originalInterOpen
     *
     * Return boolean
     */
    public function processUpdate($originalInterOpen)
    {
        $originalWrs = array();
        $originalHints = array();

        foreach ($originalInterOpen->getWordResponses() as $wr) {
            $originalWrs[] = $wr;
        }
        foreach ($originalInterOpen->getInteraction()->getHints() as $hint) {
            $originalHints[] = $hint;
        }

        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                $this->onSuccessUpdate($this->form->getData(), $originalWrs, $originalHints);

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
        $interOpen = $arg_list[0];
        $originalWrs = $arg_list[1];
        $originalHints = $arg_list[2];

        foreach ($interOpen->getWordResponses() as $wr) {
            foreach ($originalWrs as $key => $toDel) {
                if ($toDel->getId() == $wr->getId()) {
                    unset($originalWrs[$key]);
                }
            }
        }

        foreach ($originalWrs as $wr) {
            $interOpen->getWordResponses()->removeElement($wr);
            $this->em->remove($wr);
        }

        $this->modifyHints($interOpen, $originalHints);

        $this->em->persist($interOpen);
        $this->em->persist($interOpen->getInteraction()->getQuestion());
        $this->em->persist($interOpen->getInteraction());

        foreach ($interOpen->getWordResponses() as $wr) {
            $interOpen->addWordResponse($wr);
            $this->em->persist($wr);
        }

        $this->em->flush();

    }
}
