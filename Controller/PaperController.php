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

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Form\PaperType;

/**
 * Paper controller.
 *
 */
class PaperController extends Controller
{
    /**
     * Lists all Paper entities.
     *
     */
    public function indexAction($exoID)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getEntityManager();
        $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
        $workspace = $exercise->getWorkspace();

        $subscription = $this->getSubscription($user, $exoID);

        if (count($subscription) < 1) {
            return $this->redirect($this->generateUrl('exercise_show', array('id' => $exoID)));
        }

        if ($subscription[0]->getAdmin() == 1) {
            $papers = $this->getDoctrine()
                            ->getEntityManager()
                            ->getRepository('UJMExoBundle:Paper')
                            ->getExerciseAllPapers($exoID);
        } else {
            $papers = $this->getDoctrine()
                            ->getEntityManager()
                            ->getRepository('UJMExoBundle:Paper')
                            ->getExerciseUserPapers($user->getId(), $exoID);
        }

        return $this->render(
            'UJMExoBundle:Paper:index.html.twig',
            array(
                'workspace' => $workspace,
                'papers'    => $papers,
                'isAdmin'   => $subscription[0]->getAdmin(),
            )
        );
    }

    /**
     * Finds and displays a Paper entity.
     *
     */
    public function showAction($id)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $paper = $em->getRepository('UJMExoBundle:Paper')->find($id);

        $subscription = $this->getSubscription($user, $paper->getExercise()->getId());

        $worspace = $paper->getExercise()->getWorkspace();

        if (($subscription[0]->getAdmin() != 1) && ($paper->getEnd() == null)) {

            return $this->redirect($this->generateUrl('exercise_show', array('id' => $paper->getExercise()->getId())));

        } else if (($subscription[0]->getAdmin() == 1) || (($user->getId() == $paper->getUser()->getId()) &&
            (($paper->getExercise()->getCorrectionMode() == 1) || 
            (($paper->getExercise()->getCorrectionMode() == 3) &&
            ($paper->getExercise()->getDateCorrection()->format('Y-m-d H:i:s') <= date("Y-m-d H:i:s"))) || 
            (($paper->getExercise()->getCorrectionMode() == 2) &&
            ($paper->getExercise()->getMaxAttemps() <= $this->container->get('UJM_Exo.exerciseServices')->getNbPaper(
                $user->getId(), $paper->getExercise()->getId())))))
        ) {

            $display = 'all';

        } else if (($user->getId() == $paper->getUser()->getId()) && ($paper->getExercise()->getMarkMode() == 2)) {
            //si pas le droit à la correction mais que la note peut être affiché à la fin du test
            $display = 'score';
        } else {
            return $this->redirect($this->generateUrl('exercise_show', array('id' => $paper->getExercise()->getId())));
        }

        $interactions = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('UJMExoBundle:Interaction')
            ->getPaperInteraction($em, str_replace(';', '\',\'', substr($paper->getOrdreQuestion(), 0, -1)));

        $interactions = $this->orderInteractions($interactions, $paper->getOrdreQuestion());

        $responses = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('UJMExoBundle:Response')
            ->getPaperResponses($paper->getUser()->getId(), $id);

        $responses = $this->orderResponses($responses, $paper->getOrdreQuestion());

        $hintViewed = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('UJMExoBundle:LinkHintPaper')
            ->getHintViewed($paper->getId());

        return $this->render(
            'UJMExoBundle:Paper:show.html.twig',
            array(
                'workspace'    => $worspace,
                'exoId'        => $paper->getExercise()->getId(),
                'interactions' => $interactions,
                'responses'    => $responses,
                'hintViewed'   => $hintViewed,
                'correction'   => $paper->getExercise()->getCorrectionMode(),
                'display'      => $display,
            )
        );
    }

    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('UJMExoBundle:Paper')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Paper entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('paper'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
                    ->add('id', 'hidden')
                    ->getForm();
    }

    private function orderInteractions($interactions, $order)
    {
        $inter = array();
        $order = substr($order, 0, strlen($order) - 1);
        $order = explode(';', $order);

        foreach ($order as $interId) {
            foreach ($interactions as $key => $interaction) {
                if ($interaction->getId() == $interId) {
                    $inter[] = $interaction;
                    unset($interactions[$key]);
                    break;
                }
            }
        }

        return $inter;
    }

    private function orderResponses($responses, $order)
    {
        $resp = array();
        $order = substr($order, 0, strlen($order) - 1);
        $order = explode(';', $order);
        foreach ($order as $interId) {
            $tem = 0;
            foreach ($responses as $key => $response) {
                if ($response->getInteraction()->getId() == $interId) {
                    $tem++;
                    $resp[] = $response;
                    unset($responses[$key]);
                    break;
                }
            }
            //if no response
            if ($tem == 0) {
                $response = new response();
                $response->setResponse('');
                $response->setMark(0);

                $resp[] = $response;
            }
        }

        return $resp;
    }

    private function getSubscription($user, $exoID)
    {
        $subscription = $this->getDoctrine()
                             ->getEntityManager()
                             ->getRepository('UJMExoBundle:Subscription')
                             ->getControlExerciseEnroll($user->getId(), $exoID);

        return $subscription;
    }
}