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

use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Form\ChoiceType;

/**
 * Choice controller.
 *
 */
class ChoiceController extends Controller
{
    /**
     * Lists all Choice entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UJMExoBundle:Choice')->findAll();

        return $this->render(
            'UJMExoBundle:Choice:index.html.twig', array(
            'entities' => $entities
            )
        );
    }

    /**
     * Finds and displays a Choice entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:Choice')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Choice entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'UJMExoBundle:Choice:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Displays a form to create a new Choice entity.
     *
     */
    public function newAction()
    {
        $entity = new Choice();
        $form   = $this->createForm(new ChoiceType(), $entity);

        return $this->render(
            'UJMExoBundle:Choice:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
            )
        );
    }

    /**
     * Creates a new Choice entity.
     *
     */
    public function createAction()
    {
        $entity  = new Choice();
        $request = $this->getRequest();
        $form    = $this->createForm(new ChoiceType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('choice_show', array('id' => $entity->getId())));
        }

        return $this->render(
            'UJMExoBundle:Choice:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
            )
        );
    }

    /**
     * Displays a form to edit an existing Choice entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:Choice')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Choice entity.');
        }

        $editForm = $this->createForm(new ChoiceType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'UJMExoBundle:Choice:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Edits an existing Choice entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:Choice')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Choice entity.');
        }

        $editForm   = $this->createForm(new ChoiceType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('choice_edit', array('id' => $id)));
        }

        return $this->render(
            'UJMExoBundle:Choice:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Deletes a Choice entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UJMExoBundle:Choice')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Choice entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('choice'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}