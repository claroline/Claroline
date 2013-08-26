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

use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Form\InteractionOpenType;
use UJM\ExoBundle\Form\InteractionOpenHandler;

/**
 * InteractionOpen controller.
 *
 */
class InteractionOpenController extends Controller
{

    /**
     * Lists all InteractionOpen entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UJMExoBundle:InteractionOpen')->findAll();

        return $this->render('UJMExoBundle:InteractionOpen:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a InteractionOpen entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionOpen')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionOpen entity.');
        }

        return $this->render('UJMExoBundle:InteractionOpen:show.html.twig', array(
            'entity'      => $entity,
        ));
    }
    
    /**
     * Creates a new InteractionOpen entity.
     *
     */
    public function createAction()
    {
        $interOpen  = new InteractionOpen();
        $form      = $this->createForm(
            new InteractionOpenType(
                $this->container->get('security.context')->getToken()->getUser()
            ), $interOpen
        );
        
        $exoID = $this->container->get('request')->request->get('exercise');
        
        $formHandler = new InteractionOpenHandler(
            $form, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('security.context')->getToken()->getUser(), $exoID
        );
        
        if ($formHandler->processAdd()) {
            $categoryToFind = $interOpen->getInteraction()->getQuestion()->getCategory();
            $titleToFind = $interOpen->getInteraction()->getQuestion()->getTitle();

            if ($exoID == -1) {
                return $this->redirect(
                    $this->generateUrl('ujm_question_index', array(
                        'categoryToFind' => $categoryToFind, 'titleToFind' => $titleToFind)
                    )
                );
            } else {
                $this->container->get('ujm.exercise_services')->setExerciseQuestion($exoID, $interOpen);
                return $this->redirect(
                    $this->generateUrl('ujm_exercise_questions', array(
                        'id' => $exoID, 'categoryToFind' => $categoryToFind, 'titleToFind' => $titleToFind)
                    )
                );
            }
        }
        
        $formWithError = $this->render(
            'UJMExoBundle:InteractionOpen:new.html.twig', array(
            'entity' => $interOpen,
            'form'   => $form->createView(),
            'exoID'  => $exoID,
            'error'  => true
            )
        );

        $formWithError = substr($formWithError, strrpos($formWithError, 'GMT') + 3);

        return $this->render(
            'UJMExoBundle:Question:new.html.twig', array(
            'formWithError' => $formWithError
            )
        );
    }
    
    /**
     * To test the open question by the teacher
     *
     */
    public function responseOpenAction()
    {
        $request = $this->get('request');
        
        $exerciseSer = $this->container->get('ujm.exercise_services');
        $res = $exerciseSer->responseOpen($request);
        
        return $this->render(
            'UJMExoBundle:InteractionOpen:openOverview.html.twig', array(
            'interOpen'   => $res['interOpen'],
            'penalty'     => $res['penalty'],
            'response'    => $res['response'],
            'score'       => $res['score'],
            'tempMark'    => $res['tempMark']
            )
        );
    }
}
