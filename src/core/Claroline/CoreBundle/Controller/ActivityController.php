<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
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
        $repoResource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $repoResource->find($resourceId);
        $activity = $repoResource->find($activityId);
        $repoResActivity = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity');
        $link = new ResourceActivity();
        $link->setActivity($activity);
        $link->setResource($resource);
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')->findBy(array('activity' => $activityId));
        $order = count($resourceActivities) + 1;
        $link->setSequenceOrder($order);
        $em->persist($link);
        $em->flush();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->get('claroline.resource.converter')->ResourceToJson($resource));

        return $response;
    }

    public function removeResourceAction($resourceId, $activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity');
        $resourceActivity = $repo->findOneBy(array('resource' => $resourceId, 'activity' => $activityId));
        $em->remove($resourceActivity);
        $em->flush();

        return new Response('success', 204);
    }

    //dql optimization must be done later to get resource activities
    public function setSequenceOrderAction($activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')->findBy(array('activity' => $activityId));
        $params = $this->get('request')->query->all();

        foreach($resourceActivities as $resourceActivity){
            foreach($params['ids'] as $key => $id){
                if ($id == $resourceActivity->getResource()->getId()) {
                    $resourceActivity->setSequenceOrder($key);
                    $em->persist($resourceActivity);
                }
            }
        }

        $em->flush();

        return new Response('success');
    }
}
