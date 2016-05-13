<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
}
