<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Paper controller.
 */
class PaperController extends Controller
{
    /**
     * To display the modal to mark an open question.
     *
     *
     * @param int $respid   id of reponse
     * @param int $maxScore score maximun for the open question
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markedOpenAction($respid, $maxScore)
    {
        return $this->render(
                        'UJMExoBundle:Paper:q_open_mark.html.twig', array(
                    'respid' => $respid,
                    'maxScore' => $maxScore,
                        )
        );
    }

    /**
     * To record the score for a response of an open question.
     *
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function markedOpenRecordAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            /** @var \UJM\ExoBundle\Entity\Response $response */
            $response = $em->getRepository('UJMExoBundle:Response')->find($request->get('respid'));

            $response->setMark($request->get('mark'));

            $em->persist($response);
            $em->flush();

            $this->container->get('ujm.exo_exercise')->manageEndOfExercise($response->getPaper());

            return new Response($response->getId());
        } else {
            return new Response('Error');
        }
    }

    /**
     * To check the right to open exo or not.
     *
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exo
     */
    private function checkAccess($exo)
    {
        $collection = new ResourceCollection(array($exo->getResourceNode()));

        if (!$this->get('security.authorization_checker')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
