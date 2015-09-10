<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use Claroline\CoreBundle\Entity\User;

use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Subscription;

class ExerciseHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $user;
    protected $action;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Symfony\Component\Form\Form $form for an exercise
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param Doctrine EntityManager $em
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param String $action
     *
     */
    public function __construct(Form $form, Request $request, EntityManager $em, User $user, $action)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
        $this->user    = $user;
        $this->action  = $action;
    }

    /**
     * Verify if the form Exercise is valid and call the method to create an Exercise
     *
     * @access public
     *
     * Return boolean
     */
    public function process()
    {
        if ($this->request->getMethod() == 'POST') {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($this->form->getData());

                return true;
            }
        }

        return false;
    }

    /**
     * Create an Exercise
     *
     * @access private
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise exercise to create
     */
    private function onSuccess(Exercise $exercise)
    {
        // \ pour instancier un objet du namespace global et non pas de l'actuel
        $exercise->setNbQuestionPage(1);
        $this->em->persist($exercise);
        $this->em->flush();

        if ($this->action == 'add') {
            $subscription = new Subscription($this->user, $exercise);
            $subscription->setAdmin(1);
            $subscription->setCreator(1);

            $this->em->persist($subscription);

            $this->em->flush();
        }
    }
}
