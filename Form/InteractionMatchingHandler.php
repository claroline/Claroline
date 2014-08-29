<?php

namespace UJM\ExoBundle\Form;

use UJM\ExoBundle\Entity\InteractionMatching;
use UJM\ExoBundle\Form\InteractionHandler;

class InteractionMatchingHandler extends InteractionHandler
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

            if ( $this->validateNbClone() === FALSE ) {
                return 'infoDuplicateQuestion';
            }

            if ( $this->form->isValid() ) {
                $this->onSuccessAdd($this->form->getData());
            }
        }
    }

    /**
     * Implements the abstract Method
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\InteractionMatching $interMatching
     *
     */
    protected function onSuccessAdd($interMatching)
    {
        
        // to instantiate an object of the global namespace, and not of the current
        $interMatching->getInteraction()->getQuestion()->setDateCreate(new \Datetime());
        $interMatching->getInteraction()->getQuestion()->setUser($this->user);
        $interMatching->getInteraction()->setType('InteractionMatching');

        $this->em->persist($interMatching);
        $this->em->persist($interMatching->getInteraction()->getQuestion());
        $this->em->persist($interMatching->getInteraction());

        // Persist all labels of interactionMatching.
        $ord = 1;
        foreach ($interMatching->getLabels() as $label) {
            $label->setOrdre($ord);
            //$interMatching->addChoice($choice);
            $label->setInteractionMatching($interMatching);
            $this->em->persist($label);
            $ord = $ord + 1;
        }
        
        $ord = 1;
        foreach ($interMatching->getProposals() as $proposal) {
            $proposal->setOrdre($ord);
            //$interMatching->addChoice($choice);
            $proposal->setInteractionMatching($interMatching);
            $this->em->persist($proposal);
            $ord = $ord + 1;
        }

        $this->persistHints($interMatching);

        $this->em->flush();

        $this->addAnExericse($interMatching);

        $this->duplicateInter($interMatching);
        
    }

    /**
     * Implements the abstract method
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\InteractionMatching $originalInterMatching
     *
     * Return boolean
     */
    public function processUpdate($originalInterMatching)
    {
        $originalLabel = array();
        $originalProposal = array();
        $originalHints = array();
        
        //create an array of currente Label of the database
        foreach ( $originalInterMatching->getLabels() as $label ) {
            $originalLabel[] = $label;
        }
        foreach ( $originalInterMatching->getProposals() as $proposal ) {
            $originalProposal[] = $proposal;
        }
        foreach ( $originalInterMatching->getInteraction()->getHints() as $hints ) {
            $originalHints[] = $hints;
        }
        
        if ( $this->request->getMethod()  == 'POST' ) {
            $this->form->HandlerRequest($this->request);
            
            if ( $this->form->isValid() ) {
                $this->onSuccessUpdate($this->form->getData(), $originalLabel, $originalProposal, $originalHints);
            }
        }
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
        $interMatching = $arg_list[0];
        $originalLabels = $arg_list[1];
        $originalProposals = $arg_list[2];
        $originalHints = $arg_list[3];

        // filter $originalLabels to contain choice no longer present
        foreach ($interMatching->getLabels() as $label) {
            foreach ($originalLabels as $key => $toDel) {
                if ($toDel->getId() == $choice->getId()) {
                    unset($originalLabels[$key]);
                }
            }
        }
        foreach ($interMatching->getProposals() as $proposal) {
            foreach ($originalProposals as $key => $toDel) {
                if ($toDel->getId() == $proposal->getId()) {
                    unset($originalProposals[$key]);
                }
            }
        }

        // remove the relationship between the choice and the interactionmatching
        foreach ($originalLabels as $label) {
            // remove the choice from the interactionmatching
            $interMatching->getLabels()->removeElement($label);

            // if you wanted to delete the Choice entirely, you can also do that
            $this->em->remove($label);
        }
        foreach ($originalProposals as $proposal) {
            $interMatching->getProposals()->removeElement($proposal);

            $this->em->remove($proposal);
        }

        $this->modifyHints($interMatching, $originalHints);

        $this->em->persist($interMatching);
        $this->em->persist($interMatching->getInteraction()->getQuestion());
        $this->em->persist($interMatching->getInteraction());

        // Persist all Labels od interactionMatching
        foreach ($interMatching->getLabels() as $label) {
            $interMatching->addChoice($label);
            $this->em->persist($label);
        }
        foreach ($interMatching->getProposals() as $proposal) {
            $interMatching->addChoice($proposal);
            $this->em->persist($proposal);
        }

        $this->em->flush();
    }
}