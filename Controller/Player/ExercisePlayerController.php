<?php

namespace UJM\ExoBundle\Controller\Player;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Entity\Player\ExercisePlayer;

/**
 * Description of ExercisePlayerController
 *
 * @author patrick
 */
class ExercisePlayerController extends Controller {

    /**
     * display an exercise player
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="ujm_player_open")
     * @Method("GET")
     * @ParamConverter("ExercisePlayer", class="UJMExoBundle:Player\ExercisePlayer")
     */
    public function openAction(ExercisePlayer $resource) {
        if (false === $this->container->get('security.context')->isGranted('OPEN', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }

        return $this->render('UJMExoBundle:Player:view.html.twig', array(
                    '_resource' => $resource
                        )
        );
    }

    /**
     * administrate an exercise player
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="ujm_player_administrate")
     * @Method("GET")
     * @ParamConverter("ExercisePlayer", class="UJMExoBundle:Player\ExercisePlayer")
     */
    public function administrateAction(ExercisePlayer $resource) {
        if (false === $this->container->get('security.context')->isGranted('ADMINISTRATE', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }

        $pages = $this->get('ujm_exo_bundle.manager.pages')->getPages($resource);

        return $this->render('UJMExoBundle:Player:edit.html.twig', array(
                    '_resource' => $resource,
                    'pages' => $pages
                        )
        );
    }

    /**
     * update an exercise player
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="ujm_player_update", options = {"expose" = true})
     * @Method("PUT")
     * @ParamConverter("ExercisePlayer", class="UJMExoBundle:Player\ExercisePlayer")
     * 
     */
    public function updateAction(ExercisePlayer $resource) {

        if (false === $this->container->get('security.context')->isGranted('EDIT', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }

        $params = array(
            'method' => 'PUT',
            'csrf_protection' => false,
        );
        // Create form
        $form = $this->container->get('form.factory')->create('exercise_player_type', $resource, $params);
        $request = $this->container->get('request');
        $form->submit($request);
        $response = array();
        if ($form->isValid()) {
            $resource = $form->getData();
            $updated = $this->get('ujm_exo_bundle.manager.exercise_player')->update($resource);
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
