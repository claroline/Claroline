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

                return true;
            }
        }

        return false;
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
        $indLabel = 1;
        $proposals = $interMatching->getProposals();

        // to instantiate an object of the global namespace, and not of the current
        $interMatching->getInteraction()->getQuestion()->setDateCreate(new \Datetime());
        $interMatching->getInteraction()->getQuestion()->setUser($this->user);
        $interMatching->getInteraction()->setType('InteractionMatching');

        $this->em->persist($interMatching);
        $this->em->persist($interMatching->getInteraction()->getQuestion());
        $this->em->persist($interMatching->getInteraction());

        // Persist all labels of interactionMatching.
        foreach ($interMatching->getLabels() as $label) {
            $label->setInteractionMatching($interMatching);
            $this->em->persist($label);

            if ($this->isClone === FALSE) {
                if(count($this->request->get($indLabel.'_correspondence')) > 0 ) {
                    foreach($this->request->get($indLabel.'_correspondence') as $indProposal) {
                        $proposals[$indProposal]->setAssociatedLabel($label);
                    }
                }
            }

            $indLabel++;
        }

        foreach ($proposals as $proposal) {
            $proposal->setInteractionMatching($interMatching);
            $this->em->persist($proposal);
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
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                $this->onSuccessUpdate($this->form->getData(), $originalLabel, $originalProposal, $originalHints);

                return TRUE;
            }
        }

        return FALSE;
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

        // filter $originalLabels to contain label no longer present
        foreach ($interMatching->getLabels() as $label) {
            foreach ($originalLabels as $key => $toDel) {
                if ($toDel->getId() == $label->getId()) {
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

        // remove the relationship between the label and the interactionmatching
        foreach ($originalLabels as $label) {
            // remove the label from the interactionmatching
            $interMatching->getLabels()->removeElement($label);

            // if you wanted to delete the Label entirely, you can also do that
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
            $interMatching->addLabel($label);
            $this->em->persist($label);
        }
        foreach ($interMatching->getProposals() as $proposal) {
            $interMatching->addProposal($proposal);
            $this->em->persist($proposal);
        }

        $this->em->flush();
    }
}