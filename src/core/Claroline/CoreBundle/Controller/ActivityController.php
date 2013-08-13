<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Controller of the user's desktop.
 */
class ActivityController extends Controller
{
    private $resourceManager;
    private $request;

    /**
     * @DI\InjectParams({
     *     "resourceManager"    = @DI\Inject("claroline.manager.resource_manager"),
     *     "request"            = @DI\Inject("request")
     * })
     */
    public function __construct(
        ResourceManager $resourceManager,
        Request $request
    )
    {
        $this->resourceManager = $resourceManager;
        $this->request = $request;
    }

    /**
     * @EXT\Route(
     *    "/{activityId}/add/resource/{nodeId}",
     *    name="claro_activity_add_resource",
          options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "node",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "nodeId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "activity",
     *      class="ClarolineCoreBundle:Resource\Activity",
     *      options={"id" = "activityId", "strictId" = true}
     * )
     *
     * Adds a resource to an activity.
     *
     * @param ResourceNode $node
     * @param Activity     $activity
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addResourceAction(ResourceNode $node, Activity $activity)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $link = new ResourceActivity();
        $link->setActivity($activity);
        $link->setResourceNode($node);
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findBy(array('activity' => $activity->getId()));
        $order = count($resourceActivities);
        $link->setSequenceOrder($order);
        $em->persist($link);
        $em->flush();

        return new JsonResponse(array($this->resourceManager->toArray($node)));
    }

    /**
     * @EXT\Route(
     *     "/{activityId}/remove/resource/{nodeId}",
     *     name="claro_activity_remove_resource",
     *     options={"expose"=true}
     * )
     *
     * Remove a resource from an activity.
     *
     * @param integer $nodeId     the node id
     * @param integer $activityId the activity id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeResourceAction($nodeId, $activityId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity');
        $resourceActivity = $repo->findOneBy(array('resourceNode' => $nodeId, 'activity' => $activityId));
        $em->remove($resourceActivity);
        $em->flush();

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
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
        $params = $this->request->query->all();

        foreach ($resourceActivities as $resourceActivity) {
            foreach ($params['ids'] as $key => $id) {
                if ($id == $resourceActivity->getResourceNode()->getId()) {
                    $resourceActivity->setSequenceOrder($key);
                    $em->persist($resourceActivity);
                }
            }
        }

        $em->flush();

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *    "/leftmenu/{activityId}",
     *    name="claro_activity_left_menu",
     *    options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "activity",
     *      class="ClarolineCoreBundle:Resource\Activity",
     *      options={"id" = "activityId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Activity/player:leftMenu.html.twig")
     *
     * Renders the left menu of the activity player.
     * Called from an iframe.
     *
     * @param Activity $activity
     *
     * @return Response
     */
    public function renderLeftMenuAction(Activity $activity)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activity);
        $totalSteps = $this->countSteps($activity, 0);
        $totalItems = $this->countItems($activity, 0);
        $totalItems++;
        $items = array('resource' => $activity, 'step' => 1, 'resources' => $this->getItems($activity));

        return array(
            'resourceActivities' => $resourceActivities,
            'activity' => $activity,
            'items' => $items,
            'totalSteps' => $totalSteps,
            'totalItems' => $totalItems
        );
    }

    /**
     * @EXT\Route (
     *     "/player/{activityId}",
     *     name="claro_activity_show_player"
     * )
     * @EXT\ParamConverter(
     *      "activity",
     *      class="ClarolineCoreBundle:Resource\Activity",
     *      options={"id" = "activityId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Activity/player:activity.html.twig")
     *
     * Shows the player layout.
     *
     * @param Activity $activity
     *
     * @return Response
     */
    public function showPlayerAction(Activity $activity)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activity);
        $resource = isset($resourceActivities[0]) ? $resourceActivities[0]->getResourceNode(): null;

        return array(
            'activity' => $activity,
            'resource' => $resource
        );
    }

    /**
     * @EXT\Route(
     *     "/instructions/{activityId}",
     *     name="claro_activity_show_instructions"
     * )
     * @EXT\ParamConverter(
     *      "activity",
     *      class="ClarolineCoreBundle:Resource\Activity",
     *      options={"id" = "activityId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Activity/player:instructions.html.twig")
     *
     * Show the instructions of an activity.
     *
     * @param Activity $activity
     *
     * @return Response
     */
    public function showInstructionsAction(Activity $activity)
    {
        return array('instructions' => $activity);
    }

    /**
     * Count the number of steps in an activity.
     * Each step is a resource.
     *
     * @param Activity $activity
     * @param integer  $countItems
     *
     * @return integer
     */
    private function countSteps(Activity $activity, $countSteps)
    {
        foreach ($activity->getResourceActivities() as $resourceActivity) {
            if ($resourceActivity->getResourceNode()->getResourceType()->getName() !== 'activity') {
                $countSteps++;
            } else {
                $countSteps = $this->countSteps($resourceActivity->getResourceNode(), $countSteps);
            }
        }

        return $countSteps;
    }

    /**
     * Count the number of items in an activity.
     * An item is either an activity (instruction) or a resource.
     *
     * @param Activity $activity
     * @param integer  $countItems
     *
     * @return integer
     */
    private function countItems(Activity $activity, $countItems)
    {
        foreach ($activity->getResourceActivities() as $resourceActivity) {
            $countItems++;

            if ($resourceActivity->getResourceNode()->getResourceType()->getName() == 'activity') {
                $countItems = $this->countItems($resourceActivity->getResourceNode(), $countItems);
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

            if ($resourceActivity->getResourceNode()->getResourceType()->getName() == 'activity') {
                $items[] = array(
                    'resource' => $resourceActivity->getResourceNode(),
                    'step' => $step,
                    'resources' => $this->getItems($resourceActivity->getResourceNode(), $step)
                );
            } else {
                $items[] = array('resource' => $resourceActivity->getResourceNode(), 'step' => $step);
            }
        }

        return $items;
    }
}
