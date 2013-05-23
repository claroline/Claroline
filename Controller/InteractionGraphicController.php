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
use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\ExerciseQuestion;

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
        $em = $this->getDoctrine()->getEntityManager();

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
        $em = $this->getDoctrine()->getEntityManager();

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
    public function newAction()
    {
        $entity = new InteractionGraphic();
        $form = $this->createForm(new InteractionGraphicType(), $entity);

        return $this->render(
            'UJMExoBundle:InteractionGraphic:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
            )
        );
    }

    /**
     * Creates a new InteractionGraphic entity.
     *
     */
    public function createAction()
    {

        $user = $this->container->get('security.context')->getToken()->getUser();

        $interGraph = new InteractionGraphic();
        $request = $this->getRequest();
        $form = $this->createForm(new InteractionGraphicType($user), $interGraph);
        $form->bindRequest($request);

        $exoID = $this->container->get('request')->request->get('exercise');

        $interGraph->getInteraction()->getQuestion()->setDateCreate(new \Datetime()); // Set Creation Date to today
        $interGraph->getInteraction()->getQuestion()->setUser($user); // add the user to the question
        $interGraph->getInteraction()->setType('InteractionGraphic'); // set the type of the question

        $width = $this->get('request')->get('imgwidth'); // Get the width of the image
        $height = $this->get('request')->get('imgheight'); // Get the height of the image

        $interGraph->setHeight($height);
        $interGraph->setWidth($width);

        $coords = $this->get('request')->get('coordsZone'); // Get the answer zones

        $coord = preg_split('[,]', $coords); // Split all informations of one answer zones into a cell
        $lengthCoord = count($coord) - 1; // Number of answer zones

        for ($i = 0; $i < $lengthCoord; $i++) {

            $inter = preg_split('[;]', $coord[$i]); // Divide the src of the answer zone and the other informations

            $before = array("-","~");
            $after = array(",",",");

            $data = str_replace($before, $after, $inter[1]); // replace separation punctuation of the informations ...

            list(${'value'.$i}, ${'point'.$i}, ${'size'.$i}) = explode(",", $data); //... in order to split informations

            // And persist it into the Database
            ${'url'.$i} = $inter[0];

            ${'value'.$i} = str_replace("_", ",", ${'value'.$i});
            ${'url'.$i} = substr(${'url'.$i}, strrpos(${'url'.$i}, '/bundles'));

            ${'shape'.$i} = $this->getShape(${'url'.$i});
            ${'color'.$i} = $this->getColor(${'url'.$i});

            ${'co'.$i} = new Coords();

            ${'co'.$i}->setValue(${'value'.$i});
            ${'co'.$i}->setShape(${'shape'.$i});
            ${'co'.$i}->setColor(${'color'.$i});
            ${'co'.$i}->setScoreCoords(${'point'.$i});
            ${'co'.$i}->setInteractionGraphic($interGraph);
            ${'co'.$i}->setSize(${'size'.$i});
        }

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($interGraph);
            $em->persist($interGraph->getInteraction()->getQuestion());
            $em->persist($interGraph->getInteraction());

            for ($i = 0; $i < $lengthCoord; $i++) {
                $em->persist(${'co'.$i});
            }
            $em->flush();

            // To associate the question to an exercise
            if ($exoID != -1) {
                $exo = $em->getRepository('UJMExoBundle:Exercise')->find($exoID);
                $eq = new ExerciseQuestion($exo, $interGraph->getInteraction()->getQuestion());

                $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq'
                    . 'WHERE eq.exercise='.$exoID;
                $query = $em->createQuery($dql);
                $maxOrdre = $query->getResult();

                $eq->setOrdre((int) $maxOrdre[0][1] + 1);
                $em->persist($eq);

                $em->flush();
            }

            if ($exoID == -1) {
                return $this->redirect($this->generateUrl('ujm_question_index'));
            } else {
                return $this->redirect($this->generateUrl('ujm_exercise_questions', array('id' => $exoID)));
            }
        }

        return $this->render(
            'UJMExoBundle:InteractionGraphic:new.html.twig', array(
            'interGraph' => $interGraph,
            'form' => $form->createView(),
            'exoID'  => $exoID,
            )
        );
    }

    /**
     * Displays a form to edit an existing InteractionGraphic entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionGraphic entity.');
        }

        $editForm = $this->createForm(new InteractionGraphicType(), $entity);
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
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionGraphic entity.');
        }

        $editForm = $this->createForm(new InteractionGraphicType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('interactiongraphic_edit', array('id' => $id)));
        }

        return $this->render(
            'UJMExoBundle:InteractionGraphic:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Deletes a InteractionGraphic entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('UJMExoBundle:InteractionGraphic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionGraphic entity.');
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
     * Display the twig view to add a new picture to the user's document.
     *
     */
    public function savePicAction()
    {
        return $this->render('UJMExoBundle:InteractionGraphic:page.html.twig');
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
     * Get the shape of the answer zone
     *
     */
    public function getShape($url)
    {
        // Recover the shape of an answer zone thanks to its src
        $temp = strrpos($url, 'graphic/') + 8;
        $chain = substr($url, $temp, 1);

        if ($chain == "r") {
            return "rectangle";
        } else if ($chain == "c") {
            return "circle";
        }
    }

    /**
     * Get the color of the answer zone
     *
     */
    public function getColor($url)
    {
        // Recover the color of an answer zone thanks to its src
        $temp = strrpos($url, '.') - 1;
        $chain = substr($url, $temp, 1);

        switch ($chain) {
            case "w" :
                return "white";
            case "g" :
                return "green";
            case "p" :
                return "purple";
            case "b" :
                return "blue";
            case "r" :
                return "red";
            case "o" :
                return "orange";
            case "y" :
                return "yellow";
            default :
                return "white";
        }
    }

    /**
     * Fired when compose an exercise
     *
     */
    public function responseGraphicAction()
    {

        $request = $this->container->get('request');
        $exerciseSer = $this->container->get('ujm.exercise_services');
        $res = $exerciseSer->responseGraphic($request);

        return $this->render(
            'UJMExoBundle:InteractionGraphic:graphicOverview.html.twig',
            array(
                'point' => $res['point'], // Score of the student without penalty
                'penalty' => $res['penalty'], // Penalty (hints)
                'interG' => $res['interG'], // The entity interaction graphic (for the id ...)
                'coords' => $res['coords'], // The coordonates of the right answer zones
                'doc' => $res['doc'], // The answer picture (label, src ...)
                'total' => $res['total'], // Score max if all answers right and no penalty
                'rep' => $res['rep'], // Coordonates of the answer zones of the student's answer
                'score' => $res['score'] // Score of the student (right answer - penalty)
            )
        );
    }
}