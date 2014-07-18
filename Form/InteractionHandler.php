<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use Claroline\CoreBundle\Entity\User;

use UJM\ExoBundle\Entity\Exercise;

abstract class InteractionHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $exoServ;
    protected $user;
    protected $exercise;
    protected $isClone = FALSE;

    public function __construct(Form $form = NULL, Request $request = NULL, EntityManager $em, $exoServ, User $user, $exercise=-1)
    {
        $this->form     = $form;
        $this->request  = $request;
        $this->em       = $em;
        $this->exoServ  = $exoServ;
        $this->user     = $user;
        $this->exercise = $exercise;
    }

    abstract public function processAdd();
    abstract protected function onSuccessAdd($interaction);

    abstract public function processUpdate($interaction);
    abstract protected function onSuccessUpdate();

    protected function persistHints($inter) {
        foreach ($inter->getInteraction()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            //$interQCM->getInteraction()->addHint($hint);
            $hint->setInteraction($inter->getInteraction());
            $this->em->persist($hint);
        }
    }

    protected function modifyHints($inter, $originalHints) {

        // filter $originalHints to contain hint no longer present
        foreach ($inter->getInteraction()->getHints() as $hint) {
            foreach ($originalHints as $key => $toDel) {
                if ($toDel->getId() == $hint->getId()) {
                    unset($originalHints[$key]);
                }
            }
        }

        // remove the relationship between the hint and the interactionqcm
        foreach ($originalHints as $hint) {
            // remove the Hint from the interactionqcm
            $inter->getInteraction()->getHints()->removeElement($hint);

            // if you wanted to delete the Hint entirely, you can also do that
            $this->em->remove($hint);
        }

        //On persite tous les hints de l'entité interaction
        foreach ($inter->getInteraction()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            $inter->getInteraction()->addHint($hint);
            $this->em->persist($hint);
        }
    }

    protected function addAnExericse($inter) {
        if ($this->exercise != -1) {
            $exercise = $this->em->getRepository('UJMExoBundle:Exercise')->find($this->exercise);

            if ($this->exoServ->isExerciseAdmin($exercise)) {
                $this->exoServ->setExerciseQuestion($this->exercise, $inter);
            }
        }
    }

    protected function duplicateInter($inter) {
        $request = $this->request;
        if ($this->isClone === FALSE && $request->request->get('nbq') > 0)
        {
            $nbCop = 0;
            while ($nbCop < $request->request->get('nbq')) {
                $nbCop ++;
                $this->singleDuplicateInter($inter);
            }
        }
    }

    protected function validateNbClone() {

        $int =  $this->request->request->get('nbq');
        if (preg_match('/^[0-9]$/', $int)) {
            if ($int>=0 && $int<= 10 ) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function singleDuplicateInter($inter) {
        $copy = clone $inter;
        $title = $copy->getInteraction()->getQuestion()->getTitle();
        $copy->getInteraction()->getQuestion()
             ->setTitle($title.' #');

        $this->isClone = TRUE;
        $this->onSuccessAdd($copy);
    }
}