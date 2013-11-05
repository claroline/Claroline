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
use Symfony\Component\HttpFoundation\Response;
use UJM\ExoBundle\Entity\InteractionGraphic;
use UJM\ExoBundle\Form\InteractionGraphicType;
use UJM\ExoBundle\Form\InteractionGraphicHandler;

/**
 * InteractionGraphic controller.
 *
 */
class InteractionGraphicController extends Controller
{

    /**
     * Lists all InteractionGraphic entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UJMExoBundle:InteractionGraphic')->findAll();

        return $this->render(
            'UJMExoBundle:InteractionGraphic:index.html.twig', array(
            'entities' => $entities
            )
        );
    }

    /**
     * Finds and displays a InteractionGraphic entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionGraphic entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'UJMExoBundle:InteractionGraphic:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Displays a form to create a new InteractionGraphic entity.
     *
     */
    /*public function newAction()
    {
        $entity = new InteractionGraphic();
        $form = $this->createForm(new InteractionGraphicType(), $entity);

        return $this->render(
            'UJMExoBundle:InteractionGraphic:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
            )
        );
    }*/

    /**
     * Creates a new InteractionGraphic entity.
     *
     */
    public function createAction()
    {
        $interGraph = new InteractionGraphic();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->createForm(new InteractionGraphicType($user), $interGraph);

        $exoID = $this->container->get('request')->request->get('exercise');

        $formHandler = new InteractionGraphicHandler(
            $form, $this->get('request'), $this->getDoctrine()->getManager(),
            $user, $exoID
        );

         if ($formHandler->processAdd()) {
            $categoryToFind = $interGraph->getInteraction()->getQuestion()->getCategory();
            $titleToFind = $interGraph->getInteraction()->getQuestion()->getTitle();

            if ($exoID == -1) {

                return $this->redirect(
                    $this->generateUrl(
                        'ujm_question_index', array(
                            'categoryToFind' => $categoryToFind,
                            'titleToFind' => $titleToFind
                        )
                    )
                );
            } else {
                $em = $this->getDoctrine()->getManager();
                $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);

                if ($this->container->get('ujm.exercise_services')->isExerciseAdmin($exercise)) {
                    $this->container->get('ujm.exercise_services')->setExerciseQuestion($exoID, $interGraph);
                }

                return $this->redirect(
                    $this->generateUrl(
                        'ujm_exercise_questions',
                        array(
                            'id' => $exoID,
                            'categoryToFind' => $categoryToFind,
                            'titleToFind' => $titleToFind
                        )
                    )
                );
            }
         }

        $formWithError = $this->render(
            'UJMExoBundle:InteractionGraphic:new.html.twig', array(
            'entity' => $interGraph,
            'form'   => $form->createView(),
            'error'  => true,
            'exoID'  => $exoID
            )
        );

        $formWithError = substr($formWithError, strrpos($formWithError, 'GMT') + 3);

        return $this->render(
            'UJMExoBundle:Question:new.html.twig', array(
            'formWithError' => $formWithError,
            'exoID'  => $exoID
            )
        );
    }

    /**
     * Displays a form to edit an existing InteractionGraphic entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionGraphic entity.');
        }

        $editForm = $this->createForm(
            new InteractionGraphicType(
                $this->container->get('security.context')->getToken()->getUser()
            ), $entity
        );

        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'UJMExoBundle:InteractionGraphic:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Edits an existing InteractionGraphic entity.
     *
     */
    public function updateAction($id)
    {
        $exoID = $this->container->get('request')->request->get('exercise');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionGraphic entity.');
        }

        $editForm = $this->createForm(
            new InteractionGraphicType(
                $this->container->get('security.context')->getToken()->getUser()
            ), $entity
        );

        $formHandler = new InteractionGraphicHandler(
            $editForm, $this->get('request'), $this->getDoctrine()->getManager(),
            $this->container->get('security.context')->getToken()->getUser()
        );

        if ($formHandler->processUpdate($entity)) {
            if ($exoID == -1) {

                return $this->redirect($this->generateUrl('ujm_question_index'));
            } else {

                return $this->redirect(
                    $this->generateUrl(
                        'ujm_exercise_questions',
                        array(
                            'id' => $exoID,
                        )
                    )
                );
            }
        }

        return $this->forward(
            'UJMExoBundle:Question:edit', array(
                'id' => $entity->getInteraction()->getQuestion()->getId(),
                'form' => $editForm
            )
        );
    }

    /**
     * Deletes a InteractionGraphic entity.
     *
     */
    public function deleteAction($id, $pageNow)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $interactionGraphic = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($id);
        $coords = $em->getRepository('UJMExoBundle:Coords')->findBy(array('interactionGraphic' => $id));

        if (!$interactionGraphic) {
            throw $this->createNotFoundException('Unable to find InteractionGraphic entity.');
        }

        if (!$coords) {
            throw $this->createNotFoundException('Unable to find Coords link to interactiongraphic.');
        }

        $stop = count($coords);
        for ($i = 0; $i < $stop; $i++) {
            $em->remove($coords[$i]);
        }

        $em->remove($interactionGraphic);
        $em->flush();

        return $this->redirect($this->generateUrl('ujm_question_index', array('pageNow' => $pageNow)));
    }

    /**
     * Display the twig view to add a new picture to the user's document.
     *
     */
    public function savePicAction()
    {
        return $this->render('UJMExoBundle:InteractionGraphic:add_picture.html.twig');
    }

    /**
     * Get the adress of the selected picture in order to display it.
     *
     */
    public function displayPicAction()
    {
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {
            $label = $request->request->get('value'); // Name of the picture
            $prefix = $request->request->get('prefix'); // Beginning of the src of the picture

            // If the sended label isn't empty, get the matching adress
            if ($label) {
                $repository = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Document');

                $pic = $repository->findOneBy(array('label' => $label));
                $suffix = substr($pic->getUrl(), 9); // Get the end of the src of the picture
            } else {
                $suffix = ""; // Else don't display anything
            }
        }

        $url = $prefix . $suffix; // Concatenate the beginning and the end of the src of the picture

        return new Response($url); // Send back the src if the picture
    }

    /**
     * Fired when compose an exercise
     *
     */
    public function responseGraphicAction()
    {
        $vars = array();
        $request = $this->container->get('request');
        $postVal = $req = $request->request->all();

        if ($postVal['exoID'] != -1) {
            $exercise = $this->getDoctrine()->getManager()->getRepository('UJMExoBundle:Exercise')->find($postVal['exoID']);
            $vars['_resource'] = $exercise;
        }

        $exerciseSer = $this->container->get('ujm.exercise_services');
        $res = $exerciseSer->responseGraphic($request);

        $vars['point']   = $res['point']; // Score of the student without penalty
        $vars['penalty'] = $res['penalty']; // Penalty (hints)
        $vars['interG']  = $res['interG']; // The entity interaction graphic (for the id ...)
        $vars['coords']  = $res['coords']; // The coordonates of the right answer zones
        $vars['doc']     = $res['doc']; // The answer picture (label, src ...)
        $vars['total']   = $res['total']; // Score max if all answers right and no penalty
        $vars['rep']     = $res['rep']; // Coordonates of the answer zones of the student's answer
        $vars['score']   = $res['score']; // Score of the student (right answer - penalty)
        $vars['exoID']   = $postVal['exoID'];

        return $this->render('UJMExoBundle:InteractionGraphic:graphicOverview.html.twig', $vars);
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}
