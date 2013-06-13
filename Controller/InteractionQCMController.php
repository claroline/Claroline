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

use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Form\InteractionQCMType;
use UJM\ExoBundle\Form\InteractionQCMHandler;

/**
 * InteractionQCM controller.
 *
 */
class InteractionQCMController extends Controller
{
    /**
     * Lists all InteractionQCM entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UJMExoBundle:InteractionQCM')->findAll();

        return $this->render(
            'UJMExoBundle:InteractionQCM:index.html.twig', array(
            'entities' => $entities
            )
        );
    }

    /**
     * Finds and displays a InteractionQCM entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionQCM')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionQCM entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'UJMExoBundle:InteractionQCM:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Displays a form to create a new InteractionQCM entity.
     *
     */
    public function newAction()
    {
        $entity = new InteractionQCM($this->container->get('security.context')->getToken()->getUser());
        $form   = $this->createForm(
            new InteractionQCMType(
                $this->container->get('security.context')->getToken()->getUser()
            ), $entity
        );

        return $this->render(
            'UJMExoBundle:InteractionQCM:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Creates a new InteractionQCM entity.
     *
     */
    public function createAction()
    {
        $interQCM  = new InteractionQCM();
        $form      = $this->createForm(
            new InteractionQCMType(
                $this->container->get('security.context')->getToken()->getUser()
            ), $interQCM
        );

        $exoID = $this->container->get('request')->request->get('exercise');

        $formHandler = new InteractionQCMHandler(
            $form, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('security.context')->getToken()->getUser(), $exoID
        );

        if ($formHandler->processAdd()) {
            //return $this->redirect($this->generateUrl('question_show', array(
            //  'id' => $interQCM->getInteraction()->getQuestion()->getId(), 'paper' => 0)) );
            if ($exoID == -1) {
                return $this->redirect($this->generateUrl('ujm_question_index'));
            } else {
                return $this->redirect($this->generateUrl('ujm_exercise_questions', array('id' => $exoID)));
            }
        }

        return $this->render(
            'UJMExoBundle:InteractionQCM:new.html.twig', array(
            'entity' => $interQCM,
            'form'   => $form->createView(),
            'exoID'  => $exoID,
            )
        );
    }

    /**
     * Displays a form to edit an existing InteractionQCM entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionQCM')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionQCM entity.');
        }

        $editForm = $this->createForm(
            new InteractionQCMType(
                $this->container->get('security.context')->getToken()->getUser()
            ), $entity
        );
        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'UJMExoBundle:InteractionQCM:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Edits an existing InteractionQCM entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $interQCM = $em->getRepository('UJMExoBundle:InteractionQCM')->find($id);

        if (!$interQCM) {
            throw $this->createNotFoundException('Unable to find InteractionQCM entity.');
        }

        $editForm   = $this->createForm(
            new InteractionQCMType(
                $this->container->get('security.context')->getToken()->getUser()
            ), $interQCM
        );
        $formHandler = new InteractionQCMHandler(
            $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('security.context')->getToken()->getUser()
        );

        if ($formHandler->processUpdate($interQCM)) {
            return $this->redirect($this->generateUrl('ujm_question_index'));
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'UJMExoBundle:InteractionQCM:edit.html.twig', array(
            'entity'      => $interQCM,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Deletes a InteractionQCM entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UJMExoBundle:InteractionQCM')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionQCM entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_index'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }

    /**
     * To test the QCM by the teacher
     *
     */
    public function responseQcmAction()
    {
        $request = $this->get('request');
        $exerciseSer = $this->container->get('ujm.exercise_services');
        $res = $exerciseSer->responseQCM($request);

        return $this->render(
            'UJMExoBundle:InteractionQCM:qcmOverview.html.twig', array(
            'score'      => $res['score'],
            'penalty'    => $res['penalty'],
            'interQCM'   => $res['interQCM'],
            'response'   => $res['response']
            )
        );
    }
}