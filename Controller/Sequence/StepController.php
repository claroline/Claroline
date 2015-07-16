<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Entity\Sequence\Sequence;

/**
 * Description of StepController
 */
class StepController extends Controller {

    /**
     * update exercise player steps
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="ujm_steps_update", options = {"expose" = true})
     * @Method("POST")
     * @ParamConverter("Sequence", class="UJMExoBundle:Sequence\Sequence")
     * 
     */
    public function updateStepsAction(Sequence $resource) {

        if (false === $this->container->get('security.context')->isGranted('EDIT', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }
        $request = $this->container->get('request');
        // get request data
        $steps = $request->request->get('steps');

        // response
        $response = array();

        // update the exercise player pages
        try {
            $updated = $this->get('ujm_exo_bundle.manager.steps')->updateSteps($resource, $steps);
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
