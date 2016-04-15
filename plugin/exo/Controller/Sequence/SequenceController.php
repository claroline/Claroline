<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Entity\Exercise;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;

/**
 * Description of SequenceController.
 *
 * @deprecated
 */
class SequenceController extends Controller
{
    /**
     * handle all AngularServices errors.
     *
     * @Route("/error/", name="ujm_sequence_error", options={"expose"=true})
     * @Method("GET")
     */
    public function sequenceError(Request $request)
    {
        $message = $request->get('message');
        $code = $request->get('code');

        switch ($code) {
            case '403':
                throw new AccessDeniedHttpException($message);
                break;
            default :
                 throw new NotFoundHttpException($code.' '.$message);
        }
    }

    private function isExerciseAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode()));

        return $this->container->get('security.authorization_checker')->isGranted('ADMINISTRATE', $collection);
    }
}
