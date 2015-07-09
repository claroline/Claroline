<?php

namespace UJM\ExoBundle\Controller\Player;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Entity\Player\ExercisePlayer;

/**
 * Description of ExercisePlayerPageController
 */
class ExercisePlayerPageController extends Controller {

    /**
     * update exercise player pages
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="ujm_pages_update", options = {"expose" = true})
     * @Method("POST")
     * @ParamConverter("ExercisePlayer", class="UJMExoBundle:Player\ExercisePlayer")
     * 
     */
    public function updatePagesAction(ExercisePlayer $resource) {

        if (false === $this->container->get('security.context')->isGranted('EDIT', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }
        $request = $this->container->get('request');
        // get request data
        $pages = $request->request->get('pages');

        // response
        $response = array();

        // update the exercise player pages
        try {
            $updated = $this->get('ujm_exo_bundle.manager.pages')->updatePages($resource, $pages);
            $response['status'] = 'success';
            $response['messages'] = array();
            $response['data'] = $updated;
        } catch (\Exception $ex) {
            $response['status'] = 'error';
            $response['messages'] = $ex->getMessage();
            $response['data'] = null;
        }
        return new JsonResponse($response);
    }

   

}
