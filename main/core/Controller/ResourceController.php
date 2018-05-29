<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @EXT\Route("/resources", options={"expose"=true})
 */
class ResourceController
{
    /** @var ResourceManager */
    private $resourceManager;

    /** @var ResourceActionManager */
    private $actionManager;

    /**
     * ResourceController constructor.
     *
     * @DI\InjectParams({
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "actionManager"   = @DI\Inject("claroline.manager.resource_action")
     * })
     *
     * @param ResourceManager       $resourceManager
     * @param ResourceActionManager $actionManager
     */
    public function __construct(
        ResourceManager $resourceManager,
        ResourceActionManager $actionManager)
    {
        $this->resourceManager = $resourceManager;
        $this->actionManager = $actionManager;
    }

    /**
     * Executes an action on one resource.
     *
     * @EXT\Route("/{action}/{id}", name="claro_resource_action_short")
     * @EXT\Route("/{resourceType}/{action}/{id}", name="claro_resource_action")
     *
     * @param string       $action
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function objectAction($action, ResourceNode $resourceNode, Request $request)
    {
        // retrieve the resource instance
        $resource = $this->resourceManager->getResourceFromNode($resourceNode);

        // check the requested action exists
        if (!$this->actionManager->support($resourceNode, $action, $request->getMethod())) {
            // undefined action
            throw new NotFoundHttpException(
                sprintf('The action %s with method [%s] does not exist for resource type %s.', $action, $request->getMethod(), $resourceNode->getResourceType()->getName())
            );
        }

        // check current user rights
        $this->checkAccess($this->actionManager->get($resourceNode, $action), [$resourceNode]);

        // read request and get user query
        $parameters = $request->query->all();
        $content = null;
        if (!empty($request->getContent())) {
            $content = json_decode($request->getContent(), true);
        }

        // dispatch action event
        return $this->actionManager->execute($resourceNode, $action, $parameters, $content);
    }

    public function collectionAction(Request $request)
    {

    }

    /**
     * Checks the current user can execute the action on the requested nodes.
     *
     * @param MenuAction $action
     * @param array      $resourceNodes
     */
    private function checkAccess(MenuAction $action, array $resourceNodes)
    {
        $collection = new ResourceCollection($resourceNodes);
        if (!$this->actionManager->hasPermission($action, $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }
    }
}
