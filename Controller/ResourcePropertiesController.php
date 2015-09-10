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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ResourceIconType;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\CoreBundle\Form\ResourcePropertiesType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ResourcePropertiesController extends Controller
{

    private $formFactory;
    private $tokenStorage;
    private $authorization;
    private $resourceManager;
    private $request;
    private $dispatcher;

    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct
    (
        FormFactory $formFactory,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ResourceManager $resourceManager,
        Request $request,
        StrictDispatcher $dispatcher
    )
    {
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->resourceManager = $resourceManager;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @EXT\Route(
     *     "/rename/form/{node}",
     *     name="claro_resource_rename_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:renameForm.html.twig")
     *
     * Displays the form allowing to rename a resource.
     *
     * @param ResourceNode $node
     *
     * @return Response
     */
    public function renameFormAction(ResourceNode $node)
    {
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('EDIT', $collection);
        $form = $this->formFactory->create(new ResourceNameType(), $node);

        return array('form' => $form->createView(), 'nodeId' => $node->getId());
    }

    /**
     * @EXT\Route(
     *     "/rename/{node}",
     *     name="claro_resource_rename",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:renameForm.html.twig")
     *
     * Renames a resource.
     *
     * @param ResourceNode $node
     *
     * @return Response
     */
    public function renameAction(ResourceNode $node)
    {
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('EDIT', $collection);
        $form = $this->formFactory->create(new ResourceNameType(), $node);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->resourceManager->rename($node, $form->get('name')->getData());

            return new JsonResponse(
                array(
                    'id' => $node->getId(),
                    'name' => $node->getName()
                )
            );
        }

        return array('form' => $form->createView(), 'nodeId' => $node->getId());
    }

    /**
     * @EXT\Route(
     *     "/properties/form/{node}",
     *     name="claro_resource_form_properties",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:propertiesForm.html.twig")
     *
     * Displays the resource properties form.
     *
     * @param ResourceNode $node
     *
     * @return Response
     */
    public function propertiesFormAction(ResourceNode $node)
    {
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('EDIT', $collection);
        $username = $node->getCreator()->getUsername();
        $isDir = $node->getResourceType()->getName() === 'directory';

        $form = $this->formFactory->create(
            new ResourcePropertiesType($username),
            $node
        );

        return array(
            'form' => $form->createView(),
            'nodeId' => $node->getId(),
            'isDir' => $isDir
        );
    }

    /**
     * @EXT\Route(
     *     "/properties/edit/{node}",
     *     name="claro_resource_edit_properties",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:propertiesForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Changes the resource properties.
     *
     * @param ResourceNode $node
     * @param \Claroline\CoreBundle\Entity\User $user
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return SResponse
     */
    public function changePropertiesAction(ResourceNode $node, User $user)
    {
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('EDIT', $collection);
        $creatorUsername = $node->getCreator()->getUsername();
        $wasPublished = $node->isPublished();
        $form = $this->formFactory->create(
            new ResourcePropertiesType($creatorUsername),
            $node
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $file = $form->get('newIcon')->getData();
            $isRecursive = $this->request->get('isRecursive');

            if ($file) {
                $this->resourceManager->changeIcon($node, $file);
            }

            $this->resourceManager->rename($node, $name);

            if ($isRecursive) {
                $accessibleFrom = $form->get('accessibleFrom')->getData();
                $accessibleUntil = $form->get('accessibleUntil')->getData();

                $this->resourceManager
                    ->changeAccessibilityDate($node, $accessibleFrom, $accessibleUntil);
            }

            if ($node->isPublished() !== $wasPublished) {
                $eventName = "publication_change_{$node->getResourceType()->getName()}";
                $resource = $this->resourceManager->getResourceFromNode($node);
                $this->dispatcher->dispatch($eventName, 'PublicationChange', [$resource]);
            }

            $arrayNode = $this->resourceManager->toArray($node, $this->tokenStorage->getToken());

            return new JsonResponse($arrayNode);
        }

        $isDir = $node->getResourceType()->getName() === 'directory';

        return array(
            'form' => $form->createView(),
            'nodeId' => $node->getId(),
            'isDir' => $isDir
        );
    }

    /**
     * @EXT\Route(
     *     "/node/{node}/icon/edit/form",
     *     name="claro_resource_icon_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:iconEditForm.html.twig")
     *
     * Displays the resource properties form.
     *
     * @param ResourceNode $node
     *
     * @return Response
     */
    public function iconEditFormAction(ResourceNode $node)
    {
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('EDIT', $collection);
        $username = $node->getCreator()->getUsername();
        $isDir = $node->getResourceType()->getName() === 'directory';

        $form = $this->formFactory->create(
            new ResourceIconType($username),
            $node
        );

        return array(
            'form' => $form->createView(),
            'nodeId' => $node->getId(),
            'isDir' => $isDir
        );
    }

    /**
     * @EXT\Route(
     *     "/node/{node}/icon/edit",
     *     name="claro_resource_icon_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:iconEditForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Changes the resource properties.
     *
     * @param ResourceNode $node
     * @param \Claroline\CoreBundle\Entity\User $user
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return SResponse
     */
    public function iconEditAction(ResourceNode $node, User $user)
    {
        $collection = new ResourceCollection(array($node));
        $this->checkAccess('EDIT', $collection);
        $creatorUsername = $node->getCreator()->getUsername();
        $form = $this->formFactory->create(
            new ResourceIconType($creatorUsername),
            $node
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('newIcon')->getData();

            if ($file) {
                $this->resourceManager->changeIcon($node, $file);
            }
            $arrayNode = $this->resourceManager->toArray($node, $this->tokenStorage->getToken());

            return new JsonResponse($arrayNode);
        }
        $isDir = $node->getResourceType()->getName() === 'directory';

        return array(
            'form' => $form->createView(),
            'nodeId' => $node->getId(),
            'isDir' => $isDir
        );
    }

    /**
     * Checks if the current user has the right to do an action on a ResourceCollection.
     * Be carrefull, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, $collection)
    {
        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException(print_r($collection->getErrorsForDisplay(), true));
        }
    }
}
