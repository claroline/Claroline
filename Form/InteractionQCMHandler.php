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

use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Exercise;


class InteractionQCMHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $user;
    protected $exercise;

    public function __construct(Form $form, Request $request, EntityManager $em, User $user, $exercise=-1)
    {
        $this->form     = $form;
        $this->request  = $request;
        $this->em       = $em;
        $this->user     = $user;
        $this->exercise = $exercise;
    }

    public function processAdd()
    {
        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                $this->onSuccessAdd($this->form->getData());

                return true;
            }
        }

        return false;
    }

    private function onSuccessAdd(InteractionQCM $interQCM)
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
            $interQCM->addChoice($choice);
            $this->em->persist($choice);
            $ord = $ord + 1;
            //echo($choice->getRightResponse());
        }

        //On persite tous les hints de l'entité interaction
        foreach ($interQCM->getInteraction()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            $interQCM->getInteraction()->addHint($hint);
            $this->em->persist($hint);
        }

        $this->em->flush();

        if ($this->exercise != -1) {
            $exo = $this->em->getRepository('UJMExoBundle:Exercise')->find($this->exercise);
            $eq = new ExerciseQuestion($exo, $interQCM->getInteraction()->getQuestion());

            $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
                . 'WHERE eq.exercise='.$this->exercise;
            $query = $this->em->createQuery($dql);
            $maxOrdre = $query->getResult();

            $eq->setOrdre((int) $maxOrdre[0][1] + 1);
            $this->em->persist($eq);

            $this->em->flush();
        }

    }

    public function processUpdate(InteractionQCM $originalInterQCM)
    {
        $originalChoices = array();
        $originalHints = array();

        // Create an array of the current Choice objects in the database
        foreach ($originalInterQCM->getChoices() as $choice) {
            $originalChoices[] = $choice;
        }
        foreach ($originalInterQCM->getInteraction()->getHints() as $hint) {
            $originalHints[] = $hint;
        }

        if ( $this->request->getMethod() == 'POST' ) {
            $this->form->handleRequest($this->request);

            if ( $this->form->isValid() ) {
                $this->onSuccessUpdate($this->form->getData(), $originalChoices, $originalHints);

                return true;
            }
        }

        return false;
    }

    private function onSuccessUpdate(InteractionQCM $interQCM, $originalChoices, $originalHints)
    {
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

        // filter $originalHints to contain hint no longer present
        foreach ($interQCM->getInteraction()->getHints() as $hint) {
            foreach ($originalHints as $key => $toDel) {
                if ($toDel->getId() == $hint->getId()) {
                    unset($originalHints[$key]);
                }
            }
        }

        // remove the relationship between the hint and the interactionqcm
        foreach ($originalHints as $hint) {
            // remove the Hint from the interactionqcm
            $interQCM->getInteraction()->getHints()->removeElement($hint);

            // if you wanted to delete the Hint entirely, you can also do that
            $this->em->remove($hint);
        }

        $pointsWrong = str_replace(',', '.', $interQCM->getScoreFalseResponse());
        $pointsRight = str_replace(',', '.', $interQCM->getScoreRightResponse());

        $interQCM->setScoreFalseResponse($pointsWrong);
        $interQCM->setScoreRightResponse($pointsRight);

        $this->em->persist($interQCM);
        $this->em->persist($interQCM->getInteraction()->getQuestion());
        $this->em->persist($interQCM->getInteraction());

        // On persiste tous les choices de l'interaction QCM.
        //$ord = 1;
        foreach ($interQCM->getChoices() as $choice) {
            //$choice->setOrdre($ord);
            $interQCM->addChoice($choice);
            $this->em->persist($choice);
            //$ord++;
        }

        //On persite tous les hints de l'entité interaction
        foreach ($interQCM->getInteraction()->getHints() as $hint) {
            $interQCM->getInteraction()->addHint($hint);
            $this->em->persist($hint);
        }

        $this->em->flush();

    }
}