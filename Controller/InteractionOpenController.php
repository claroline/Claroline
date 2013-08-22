<?php

namespace UJM\ExoBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use UJM\ExoBundle\Entity\InteractionOpen;

/**
 * InteractionOpen controller.
 *
 */
class InteractionOpenController extends Controller
{

    /**
     * Lists all InteractionOpen entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UJMExoBundle:InteractionOpen')->findAll();

        return $this->render('UJMExoBundle:InteractionOpen:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a InteractionOpen entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UJMExoBundle:InteractionOpen')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InteractionOpen entity.');
        }

        return $this->render('UJMExoBundle:InteractionOpen:show.html.twig', array(
            'entity'      => $entity,
        ));
    }
}
