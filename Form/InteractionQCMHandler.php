<?php

namespace UJM\ExoBundle\Form;

class InteractionQCMHandler extends \UJM\ExoBundle\Form\InteractionHandler
{
    /**
     * Implements the abstract method.
     */
    public function processAdd()
    {
        if ($this->request->getMethod() == 'POST') {
            $this->form->handleRequest($this->request);
             //Uses the default category if no category selected
            $this->checkCategory();
            //If title null, uses the first 50 characters of "invite" (enuncicate)
            $this->checkTitle();
            if ($this->validateNbClone() === false) {
                return 'infoDuplicateQuestion';
            }

            if ($this->form->isValid()) {
                $this->onSuccessAdd($this->form->getData());

                return true;
            }
        }

        return false;
    }

    /**
     * Implements the abstract method.
     *
     *
     * @param \UJM\ExoBundle\Entity\InteractionQCM $interQCM
     */
    protected function onSuccessAdd($interQCM)
    {

        // \ pour instancier un objet du namespace global et non pas de l'actuel
        $interQCM->getInteraction()->getQuestion()->setDateCreate(new \Datetime());
        $interQCM->getInteraction()->getQuestion()->setUser($this->user);
        $interQCM->getInteraction()->setType('InteractionQCM');

        $pointsWrong = str_replace(',', '.', $interQCM->getScoreFalseResponse());
        $pointsRight = str_replace(',', '.', $interQCM->getScoreRightResponse());

        $interQCM->setScoreFalseResponse($pointsWrong);
        $interQCM->setScoreRightResponse($pointsRight);

        $this->em->persist($interQCM);
        $this->em->persist($interQCM->getInteraction()->getQuestion());
        $this->em->persist($interQCM->getInteraction());

        // On persiste tous les choices de l'interaction QCM.
        $ord = 1;
        foreach ($interQCM->getChoices() as $choice) {
            $choice->setOrdre($ord);
            $choice->setInteractionQCM($interQCM);
            $this->em->persist($choice);
            $ord = $ord + 1;
        }

        $this->persistHints($interQCM);

        $this->em->flush();

        $this->addAnExercise($interQCM);

        $this->duplicateInter($interQCM);
    }

    /**
     * Implements the abstract method.
     *
     *
     * @param \UJM\ExoBundle\Entity\InteractionQCM $originalInterQCM
     *
     * Return boolean
     */
    public function processUpdate($originalInterQCM)
    {
        $originalChoices = array();
        $originalHints = array();

        // Create an array of the current Choice objects in the database
        foreach ($originalInterQCM->getChoices() as $choice) {
            $originalChoices[] = $choice;
        }
        foreach ($originalInterQCM->getQuestion()->getHints() as $hint) {
            $originalHints[] = $hint;
        }

        if ($this->request->getMethod() == 'POST') {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccessUpdate($this->form->getData(), $originalChoices, $originalHints);

                return true;
            }
        }

        return false;
    }

    /**
     * Implements the abstract method.
     */
    protected function onSuccessUpdate()
    {
        $arg_list = func_get_args();
        $interQCM = $arg_list[0];
        $originalChoices = $arg_list[1];
        $originalHints = $arg_list[2];

        // filter $originalChoices to contain choice no longer present
        foreach ($interQCM->getChoices() as $choice) {
            foreach ($originalChoices as $key => $toDel) {
                if ($toDel->getId() == $choice->getId()) {
                    unset($originalChoices[$key]);
                }
            }
        }

        // remove the relationship between the choice and the interactionqcm
        foreach ($originalChoices as $choice) {
            // remove the choice from the interactionqcm
            $interQCM->getChoices()->removeElement($choice);

            // if you wanted to delete the Choice entirely, you can also do that
            $this->em->remove($choice);
        }

        $this->modifyHints($interQCM, $originalHints);

        $pointsWrong = str_replace(',', '.', $interQCM->getScoreFalseResponse());
        $pointsRight = str_replace(',', '.', $interQCM->getScoreRightResponse());

        $interQCM->setScoreFalseResponse($pointsWrong);
        $interQCM->setScoreRightResponse($pointsRight);

        $this->em->persist($interQCM);
        $this->em->persist($interQCM->getQuestion());

        // On persiste tous les choices de l'interaction QCM.
        foreach ($interQCM->getChoices() as $choice) {
            $interQCM->addChoice($choice);
            $this->em->persist($choice);
        }

        $this->em->flush();
    }
}
