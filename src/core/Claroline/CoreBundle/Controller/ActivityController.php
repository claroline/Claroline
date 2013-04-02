<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller of the user's desktop.
 */
class ActivityController extends Controller
{
    /**
     * @Route(
     *    "/{activityId}/add/resource/{resourceId}",
     *    name="claro_activity_add_resource",
          options={"expose"=true}
     * )
     *
     * Adds a resource to an activity.
     *
     * @param type $resourceId the resource id
     * @param type $activityId the activity id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addResourceAction($resourceId, $activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repoResource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $repoResource->find($resourceId);
        $activity = $repoResource->find($activityId);
        $link = new ResourceActivity();
        $link->setActivity($activity);
        $link->setResource($resource);
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findBy(array('activity' => $activityId));
        $order = count($resourceActivities);
        $link->setSequenceOrder($order);
        $em->persist($link);
        $em->flush();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $jsonResource = $this->get('claroline.resource.converter')
            ->toJson($resource, $this->get('security.context')->getToken());
        $response->setContent($jsonResource);

        return $response;
    }

    /**
     * @Route(
     *     "/{activityId}/remove/resource/{resourceId}",
     *     name="claro_activity_remove_resource",
     *     options={"expose"=true}
     * )
     *
     * Remove a resource from an activity.
     *
     * @param type $resourceId the resource id
     * @param type $activityId the activity id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeResourceAction($resourceId, $activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity');
        $resourceActivity = $repo->findOneBy(array('resource' => $resourceId, 'activity' => $activityId));
        $em->remove($resourceActivity);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/{activityId}/set/sequence",
     *     name="claro_activity_set_sequence",
     *     options={"expose"=true}
     * )

     * Sets the order of the resource in an activity.
     * It takes an array of resourceIds as parameter (querystring: ids[]=1&ids[]=2 ...)
     *
     * @param type $activityId the activity id
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setSequenceOrderAction($activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findBy(array('activity' => $activityId));
        $params = $this->get('request')->query->all();

        foreach ($resourceActivities as $resourceActivity) {
            foreach ($params['ids'] as $key => $id) {
                if ($id == $resourceActivity->getResource()->getId()) {
                    $resourceActivity->setSequenceOrder($key);
                    $em->persist($resourceActivity);
                }
            }
        }

        $em->flush();

        return new Response('success');
    }

    /**
     * @Route(
     *    "/leftmenu/{activityId}",
     *    name="claro_activity_left_menu",
     *    options={"expose"=true}
     * )
     *
     * Renders the left menu of the activity player.
     * Called from an iframe.
     *
     * @param type $activityId the activity id
     *
     * @return Response
     */
    public function renderLeftMenuAction($activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $activity = $em->getRepository('ClarolineCoreBundle:Resource\Activity')
            ->find($activityId);
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activity);
        $totalSteps = $this->countSteps($activity, 0);
        $totalItems = $this->countItems($activity, 0);
        $totalItems++;
        $items = array('resource' => $activity, 'step' => 1, 'resources' => $this->getItems($activity));

        return $this->render(
            'ClarolineCoreBundle:Activity/player:left_menu.html.twig',
            array(
                'resourceActivities' => $resourceActivities,
                'activity' => $activity,
                'items' => $items,
                'totalSteps' => $totalSteps,
                'totalItems' => $totalItems
            )
        );
    }

    /**
     * @Route (
     *     "/player/{activityId}",
     *     name="claro_activity_show_player"
     * )
     *
     * Shows the player layout.
     *
     * @param type $activityId the activity.
     *
     * @return Response
     */
    public function showPlayerAction($activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $activity = $em->getRepository('ClarolineCoreBundle:Resource\Activity')
            ->find($activityId);
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activity);

        return $this->render(
            'ClarolineCoreBundle:Activity/player:activity.html.twig',
            array(
                'activity' => $activity,
                'resource' => $resourceActivities[0]->getResource()
            )
        );
    }

    /**
     * @Route(
     *     "/instructions/{activityId}",
     *     name="claro_activity_show_instructions"
     * )

     * Show the instructions of an activity.
     *
     * @param type $activityId the activity id
     *
     * @return Response
     */
    public function showInstructionsAction($activityId)
    {
        $activity = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\Activity')
            ->find($activityId);

        return $this->render(
            'ClarolineCoreBundle:Activity\player:instructions.html.twig',
            array('instructions' => $activity)
        );
    }

    /**
     * Count the number of steps in an activity.
     * Each step is a resource.
     *
     * @param Activity $activity
     * @param integer $countItems
     *
     * @return integer
     */
    private function countSteps(Activity $activity, $countSteps)
    {
        foreach ($activity->getResourceActivities() as $resourceActivity) {
            if ($resourceActivity->getResource()->getResourceType()->getName() !== 'activity') {
                $countSteps++;
            } else {
                $countSteps = $this->countSteps($resourceActivity->getResource(), $countSteps);
            }
        }

        return $countSteps;
    }

    /**
     * Count the number of items in an activity.
     * An item is either an activity (instruction) or a resource.
     *
     * @param Activity $activity
     * @param integer $countItems
     *
     * @return integer
     */
    private function countItems(Activity $activity, $countItems)
    {
        foreach ($activity->getResourceActivities() as $resourceActivity) {
            $countItems++;

            if ($resourceActivity->getResource()->getResourceType()->getName() == 'activity') {
                $countItems = $this->countItems($resourceActivity->getResource(), $countItems);
            }
        }

        return $countItems;
    }

    /**
     * Returns an array containing activities & resources.
     * This will be used to create the left menus href where each activity in an activity can
     * be considered as a chapter.
     *
     * /!\ pointer usage
     * @param Activity activity
     * @param $step    the current step (recursive function)
     * @param $items   the current items (recursive function)
     *
     * @return array
     */
    private function getItems(Activity $activity, &$step = 1, $items = array())
    {
        foreach ($activity->getResourceActivities() as $resourceActivity) {
            $step++;

            if ($resourceActivity->getResource()->getResourceType()->getName() == 'activity') {
                $items[] = array(
                    'resource' => $resourceActivity->getResource(),
                    'step' => $step,
                    'resources' => $this->getItems($resourceActivity->getResource(), $step)
                );
            } else {
                $items[] = array('resource' => $resourceActivity->getResource(), 'step' => $step);
            }
        }

        return $items;
    }
}