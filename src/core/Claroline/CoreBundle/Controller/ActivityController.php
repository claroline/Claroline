<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller of the user's desktop.
 */
class ActivityController extends Controller
{
    public function addResourceAction($resourceId, $activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $repo->find($resourceId);
        $activity = $repo->find($activityId);
        $activity->addResource($resource);
        $em->persist($activity);
        $em->flush();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->get('claroline.resource.converter')->ResourceToJson($resource));

        return $response;
    }

    public function removeResourceAction($resourceId, $activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $repo->find($resourceId);
        $activity = $repo->find($activityId);
        $activity->removeResource($resource);
        $em->persist($activity);
        $em->flush();

        return new Response('success', 204);
    }
}
