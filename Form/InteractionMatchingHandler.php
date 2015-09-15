<?php

namespace UJM\ExoBundle\Form;

class InteractionMatchingHandler extends QuestionHandler
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
            //If title null, uses the first 50 characters of "invite" (enuncicate)
            $this->checkTitle();           
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
        $proposals = array_merge($interMatching->getProposals()->toArray());

        // to instantiate an object of the global namespace, and not of the current
        $interMatching->getQuestion()->setDateCreate(new \Datetime());
        $interMatching->getQuestion()->setUser($this->user);

        $this->em->persist($interMatching);
        $this->em->persist($interMatching->getQuestion());

        // Persist all labels of interactionMatching.
        foreach ($interMatching->getLabels() as $label) {
            $label->setInteractionMatching($interMatching);
            $this->em->persist($label);

            if ($this->isClone === FALSE) {
                if(count($this->request->get($indLabel.'_correspondence')) > 0 ) {
                    foreach($this->request->get($indLabel.'_correspondence') as $indProposal) {
                        $proposals[$indProposal - 1]->addAssociatedLabel($label);
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

        $this->addAnExercise($interMatching);

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
        foreach ( $originalInterMatching->getQuestion()->getHints() as $hints ) {
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

        $proposals = $interMatching->getProposals();
        $indLabel = 1;

        //remove all relationships between proposal and label
        foreach ($proposals as $proposal) {
            $proposal->removeAssociatedLabel($proposal);
        }

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
        $this->em->persist($interMatching->getQuestion());

        // Persist all Labels of interactionMatching
        foreach ($interMatching->getLabels() as $label) {
            $label->setInteractionMatching($interMatching);
            $this->em->persist($label);
        }
        foreach ($interMatching->getProposals() as $proposal) {
            $proposal->setInteractionMatching($interMatching);
            $this->em->persist($proposal);
        }

        $proposals = array_merge($interMatching->getProposals()->toArray());
        foreach ($interMatching->getLabels() as $label) {
            if(count($this->request->get($indLabel.'_correspondence')) > 0 ) {
                foreach($this->request->get($indLabel.'_correspondence') as $indProposal) {
                    $proposals[$indProposal - 1]->addAssociatedLabel($label);
                    $this->em->persist($proposals[$indProposal - 1]);
                }
            }

            $indLabel++;
        }
        $this->em->flush();
    }
}
