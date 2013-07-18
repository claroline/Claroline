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

use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Subscription;

class ExerciseHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $user;
    protected $action;

    public function __construct(Form $form, Request $request, EntityManager $em, User $user, $action)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
        $this->user    = $user;
        $this->action  = $action;
    }

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

    private function onSuccess(Exercise $exercise)
    {
        // \ pour instancier un objet du namespace global et non pas de l'actuel
        $exercise->setDateCreate(new \Datetime());
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