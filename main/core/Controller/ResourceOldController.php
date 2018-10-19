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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ResourceOldController.
 *
 * @todo restore used before remove (eg. the action about lock / unlock)
 */
class ResourceOldController extends Controller
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var EventManager */
    private $eventManager;

    /**
     * ResourceOldController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "eventManager"    = @DI\Inject("claroline.event.manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ResourceManager               $resourceManager
     * @param EventManager                  $eventManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ResourceManager $resourceManager,
        EventManager $eventManager
    ) {
        $this->authorization = $authorization;
        $this->resourceManager = $resourceManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @EXT\Route(
     *     "/logs/{node}",
     *     name="claro_resource_logs",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:resource/logs:list.html.twig")
     *
     * Shows resource logs list
     *
     * @param ResourceNode $node the resource
     *
     * @return array
     *
     * @throws \Exception
     */
    public function logListAction(ResourceNode $node)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection([$node]);
        $this->checkAccess('ADMINISTRATE', $collection);

        return [
            'workspace' => $node->getWorkspace(),
            '_resource' => $resource,
            'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
        ];
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:resource:breadcrumbs.html.twig")
     *
     * @param ResourceNode $node
     *
     * @return array
     */
    public function renderBreadcrumbsAction(ResourceNode $node)
    {
        return [
            'ancestors' => $node->getAncestors(),
            'workspaceId' => $node->getWorkspace(),
        ];
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    public function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }
    }
}
