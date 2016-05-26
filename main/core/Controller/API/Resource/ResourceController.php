<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Resource;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

/**
 * @NamePrefix("api_")
 */
class ResourceController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "request"         = @DI\Inject("request"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        Request $request,
        ResourceManager $resourceManager,
        StrictDispatcher $dispatcher,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->request = $request;
        $this->resourceManager = $resourceManager;
        $this->dispatcher = $dispatcher;
    }

    public function getResourceFormAction($resourceType)
    {
        throw new \Exception('No implementation yet.');
    }

    /**
     * POST Route annotation because it'll use 'PATCH' by default.
     * php app/console router:debug submit_resource_form for routing informations.
     *
     * @Post("/resources/{resourceType}/parent/{parent}/encoding/{encoding}/submit")
     * @View(serializerGroups={"api_resource_node"})
     * Set $parent to 0 for personal workspace !
     * Encoding should be 'none' by default.
     * The form fields must be like 'file_form[name] file_form[file]' and so on...
     */
    public function submitResourceFormAction($resourceType, $parent = 0, $encoding = 'none')
    {
        /* @todo add security to kick anon out of this method*/
        $user = $this->tokenStorage->getToken()->getUser();
        $parent = (int) $parent;

        //uncomment for debug via oauth
        /*
        if ($user === 'anon.' || $user === null) {
            $user = $this->container->get('claroline.manager.user_manager')->getUserByUsername('root');
        }*/

        //not strict because it could be a string '0'
        $parent = $parent === 0 ?
            /*
             * carreful, it won't work every time because not every user has a personal workspace.
             * we'll have to handle taht case later
             */
            $this->resourceManager->getWorkspaceRoot($user->getPersonalWorkspace()) :
            $parent = $this->resourceManager->getById($parent);

        //be sure we can create resources
        //these lines won't work for oauth
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => $resourceType));

        if (!$this->authorization->isGranted('CREATE', $collection)) {
            $errors = $collection->getErrors();
            //gotta think about error handling later
        }
        //end of oauth not working

        //maybe init this from the form. I don't know. It could be removed imo.
        $isPublished = true;
        $nodes = [];

        //Handles the resource creation for any type because I'm lazy and it's better like this anyway.
        //@See FileListener for implementation
        $event = $this->dispatcher->dispatch('create_api_'.$resourceType, 'CreateResource', array($parent, $resourceType, $encoding));

        if (count($event->getResources()) > 0) {
            //Foreach is here because when we unzip a resource, we may add a crapton of stuff at one here.
            //It should have been easier if we created a root directory with everything inside.
            foreach ($event->getResources() as $resource) {
                if ($event->getProcess()) {
                    $createdResource = $this->resourceManager->create(
                        $resource,
                        $this->resourceManager->getResourceTypeByName($resourceType),
                        $user,
                        $parent->getWorkspace(),
                        $parent,
                        null,
                        array(),
                        $isPublished
                    );
                    $this->dispatcher->dispatch(
                        'resource_created_'.$resourceType,
                        'ResourceCreated',
                        array($createdResource->getResourceNode())
                    );
                    $nodes[] = $createdResource->getResourceNode();
                }
            }
        } else {
            return $event->getErrorFormContent();
        }

        return $nodes;
    }

    /**
     * @View(serializerGroups={"api_resource_node"})
     */
    public function getResourceNodeAction(ResourceNode $resourceNode)
    {
        $collection = new ResourceCollection(array($resourceNode));
        $this->checkAccess('OPEN', $collection);

        return $resourceNode;
    }

    public function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
