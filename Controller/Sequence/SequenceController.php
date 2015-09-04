<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Entity\Sequence\Sequence;
use UJM\ExoBundle\Entity\Exercise;

/**
 * Description of SequenceController
 *
 */
class SequenceController extends Controller {

    /**
     * Play the selected Exercise
     * @Route("/play/{id}", requirements={"id" = "\d+"}, name="ujm_exercise_play")
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     */
    public function playAction(Exercise $exercise) {

        $id = $exercise->getId();
        // get JSON from Controller
        $response = $this->forward('UJMExoBundle:Api\Exercise:exercise', array('id' => $id));
        $data = $response->getContent();
        return $this->render('UJMExoBundle:Sequence:play.html.twig', array('_resource' => $exercise, 'data' => $data));
    }

    /**
     * display a sequence
     * @Route("/get/{id}", requirements={"id" = "\d+"}, name="ujm_sequence_open")
     * @Method("GET")
     * @ParamConverter("Sequence", class="UJMExoBundle:Sequence\Sequence")
     */
    public function openAction(Sequence $resource) {
        if (false === $this->container->get('security.context')->isGranted('OPEN', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }

        return $this->render('UJMExoBundle:Sequence:view.html.twig', array(
                    '_resource' => $resource
                        )
        );
    }

    /**
     * administrate an exercise player
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="ujm_sequence_administrate")
     * @Method("GET")
     * @ParamConverter("Sequence", class="UJMExoBundle:Sequence\Sequence")
     */
    public function administrateAction(Sequence $resource) {
        if (false === $this->container->get('security.context')->isGranted('ADMINISTRATE', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }

        $steps = $this->get('ujm_exo_bundle.manager.steps')->getSteps($resource);

        return $this->render('UJMExoBundle:Sequence:edit.html.twig', array(
                    '_resource' => $resource,
                    'steps' => $steps
                        )
        );
    }

    /**
     * update an exercise player
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="ujm_sequence_update", options = {"expose" = true})
     * @Method("PUT")
     * @ParamConverter("Sequence", class="UJMExoBundle:Sequence\Sequence")
     * 
     */
    public function updateAction(Sequence $resource) {

        if (false === $this->container->get('security.context')->isGranted('EDIT', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }

        $params = array(
            'method' => 'PUT',
            'csrf_protection' => false,
        );
        // Create form
        $form = $this->container->get('form.factory')->create('sequence_type', $resource, $params);
        $request = $this->container->get('request');
        $form->submit($request);
        $response = array();
        if ($form->isValid()) {
            $resource = $form->getData();
            $updated = $this->get('ujm_exo_bundle.manager.sequence')->update($resource);
            $response['status'] = 'success';
            $response['messages'] = array();
            $response['data'] = $updated;
        } else {
            $errors = $this->getFormErrors($form);
            $response['status'] = 'error';
            $response['messages'] = $errors;
            $response['data'] = null;
        }
        return new JsonResponse($response);
    }

    private function getFormErrors(FormInterface $form) {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $errors[$key] = $error->getMessage();
        }
        // Get errors from children
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getFormErrors($child);
            }
        }
        return $errors;
    }

}
