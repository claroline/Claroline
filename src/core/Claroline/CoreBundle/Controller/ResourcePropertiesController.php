<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ResourcePropertiesController extends Controller
{

    private $formFactory;
    private $sc;
    private $resourceManager;
    private $request;
    private $dispatcher;

    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("claroline.form.factory"),
     *     "sc"              = @DI\Inject("security.context"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct
    (
        FormFactory $formFactory,
        SecurityContext $sc,
        ResourceManager $resourceManager,
        Request $request,
        StrictDispatcher $dispatcher
    )
    {
        $this->formFactory = $formFactory;
        $this->sc = $sc;
        $this->resourceManager = $resourceManager;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @EXT\Route(
     *     "/rename/form/{resourceId}",
     *     name="claro_resource_rename_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:renameForm.html.twig")
     * @EXT\ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceId", "strictId" = true}
     * )
     *
     * Displays the form allowing to rename a resource.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function renameFormAction(AbstractResource $resource)
    {
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $form = $this->formFactory->create(FormFactory::TYPE_RESOURCE_RENAME, array(), $resource);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/rename/{resourceId}",
     *     name="claro_resource_rename",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:renameForm.html.twig")
     * @EXT\ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceId", "strictId" = true}
     * )
     *
     * Renames a resource.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function renameAction(AbstractResource $resource)
    {
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $form = $this->formFactory->create(FormFactory::TYPE_RESOURCE_RENAME, array(), $resource);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->resourceManager->rename($resource, $form->get('name')->getData());

            return new JsonResponse(array($resource->getName()));
        }

        return array(
            'resourceId' => $resource->getId(),
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/properties/form/{resourceId}",
     *     name="claro_resource_form_properties",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:propertiesForm.html.twig")
     * @EXT\ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceId", "strictId" = true}
     * )
     *
     * Displays the resource properties form.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function propertiesFormAction(AbstractResource $resource)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_RESOURCE_PROPERTIES, array(), $resource);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/properties/edit/{resourceId}",
     *     name="claro_resource_edit_properties",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:propertiesForm.html.twig")
     * @EXT\ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\AbstractResource",
     *      options={"id" = "resourceId", "strictId" = true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Changes the resource properties.
     *
     * @param integer $resourceId the resource id
     *
     * @return StreamedResponse
     */
    public function changePropertiesAction(AbstractResource $resource, User $user)
    {
        if (!$user === $resource->getCreator()) {
             throw new AccessDeniedException("You're not the owner of this resource");
        }

        $form = $this->formFactory->create(FormFactory::TYPE_RESOURCE_PROPERTIES, array(), $resource);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $file = $form->get('newIcon')->getData();

            if ($file) {
                $icon = $this->resourceManager->changeIcon($resource, $file);
            }

            $this->resourceManager->rename($resource, $name);

            $content = "{";
            $content .= (isset($icon)) ?
                $content .= '"icon": "' . $icon->getRelativeUrl() . '"':
                $content .= '"icon": "' . $resource->getIcon()->getRelativeUrl() . '"';

            $content .= ', "name": "' . $resource->getName() . '"';
            $content .= '}';

            return new JsonResponse(array($content));
        }

        return array(
            'resourceId' => $resource->getId(),
            'form' => $form->createView()
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
        if (!$this->sc->isGranted($permission, $collection)) {
            throw new AccessDeniedException(print_r($collection->getErrorsForDisplay(), true));
        }
    }
}
