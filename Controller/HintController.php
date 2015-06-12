<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use UJM\ExoBundle\Entity\LinkHintPaper;

/**
 * Hint controller.
 *
 */
class HintController extends Controller
{

    /**
     * Finds and displays a Hint entity.
     *
     * @access public
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
    {
        $request = $this->container->get('request');
        $session = $this->getRequest()->getSession();

        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');

            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UJMExoBundle:Hint')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Hint entity.');
            }

            if (!$session->get('penalties')) {
                $penalties = array();
                $session->set('penalties', $penalties);
            }
            $penalties = $session->get('penalties');

            if (($request->request->get('paper') != null) && (!isset($penalties[$id]))) {
                $lhp = new LinkHintPaper(
                    $entity, $em->getRepository('UJMExoBundle:Paper')->find($session->get('paper'))
                );
                $lhp->setView(1);
                $em->persist($lhp);
                $em->flush();
            }

            $penalties[$id] = $entity->getPenalty();
            $session->set('penalties', $penalties);

            return $this->container->get('templating')->renderResponse(
                'UJMExoBundle:Hint:show.html.twig', array(
                'entity'      => $entity,
                )
            );
        } else {

            return 0;
        }
    }
}