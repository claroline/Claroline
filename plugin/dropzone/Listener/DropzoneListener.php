<?php

namespace Icap\DropzoneBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Icap\DropzoneBundle\Entity\Criterion;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Form\DropzoneType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class DropzoneListener
{
    private $container;
    private $httpKernel;
    private $request;
    private $resourceManager;

    /**
     * @DI\InjectParams({
     *     "container"       = @DI\Inject("service_container"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        ResourceManager $resourceManager
    ) {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->resourceManager = $resourceManager;
    }

    /**
     * @DI\Observe("create_form_icap_dropzone")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DropzoneType(), new Dropzone());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'icap_dropzone',
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_icap_dropzone")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DropzoneType(), new Dropzone());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $dropzone = $form->getData();
            $event->setResources([$dropzone]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'icap_dropzone',
                ]
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_icap_dropzone")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $collection = new ResourceCollection([$event->getResource()->getResourceNode()]);

        if (false === $this->container->get('security.authorization_checker')->isGranted('EDIT', $collection)) {
            $params['_controller'] = 'IcapDropzoneBundle:Dropzone:open';
        } else {
            $params['_controller'] = 'IcapDropzoneBundle:Dropzone:editCommon';
        }
        $params['resourceId'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_dropzone_icap_dropzone")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onOpenCustom(CustomActionResourceEvent $event)
    {
        $resource = get_class($event->getResource()) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut' ?
            $this->resourceManager->getResourceFromShortcut($event->getResource()->getResourceNode()) :
            $event->getResource();
        $params = [];
        $params['_controller'] = 'IcapDropzoneBundle:Dropzone:open';
        $params['resourceId'] = $resource->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("edit_dropzone_icap_dropzone")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onEdit(CustomActionResourceEvent $event)
    {
        $resource = get_class($event->getResource()) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut' ?
            $this->resourceManager->getResourceFromShortcut($event->getResource()->getResourceNode()) :
            $event->getResource();
        $params = [];
        $params['_controller'] = 'IcapDropzoneBundle:Dropzone:editCommon';
        $params['resourceId'] = $resource->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("list_dropzone_icap_dropzone")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onList(CustomActionResourceEvent $event)
    {
        $resource = get_class($event->getResource()) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut' ?
            $this->resourceManager->getResourceFromShortcut($event->getResource()->getResourceNode()) :
            $event->getResource();
        $params = [];
        $params['_controller'] = 'IcapDropzoneBundle:Drop:dropsByDefault';
        $params['resourceId'] = $resource->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_icap_dropzone")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();

        foreach ($resource->getDrops() as $drop) {
            $em->remove($drop);
        }

        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_icapdropzone")
     *
     * @param PluginOptionsEvent $event
     */
    public function onAdministrate(PluginOptionsEvent $event)
    {
    }

    /**
     * @DI\Observe("copy_icap_dropzone")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var Dropzone $resource */
        $resource = $event->getResource();

        $newDropzone = new Dropzone();
        $newDropzone->setName($resource->getName());
        $newDropzone->setAllowCommentInCorrection($resource->getAllowCommentInCorrection());
        $newDropzone->setAllowRichText($resource->getAllowRichText());
        $newDropzone->setAllowUpload($resource->getAllowUpload());
        $newDropzone->setAllowUrl($resource->getAllowUrl());
        $newDropzone->setAllowWorkspaceResource($resource->getAllowWorkspaceResource());
        $newDropzone->setDisplayNotationMessageToLearners($resource->getDisplayNotationMessageToLearners());
        $newDropzone->setDisplayNotationToLearners($resource->getDisplayNotationToLearners());
        $newDropzone->setEditionState($resource->getEditionState());
        $newDropzone->setEndAllowDrop($resource->getEndAllowDrop());
        $newDropzone->setEndReview($resource->getEndReview());
        $newDropzone->setExpectedTotalCorrection($resource->getExpectedTotalCorrection());
        $newDropzone->setInstruction($resource->getInstruction());
        $newDropzone->setManualPlanning($resource->getManualPlanning());
        $newDropzone->setManualState($resource->getManualState());
        $newDropzone->setMinimumScoreToPass($resource->getMinimumScoreToPass());
        $newDropzone->setPeerReview($resource->getPeerReview());
        $newDropzone->setStartAllowDrop($resource->getStartAllowDrop());
        $newDropzone->setStartReview($resource->getStartReview());
        $newDropzone->setTotalCriteriaColumn($resource->getTotalCriteriaColumn());

        $oldCriteria = $resource->getPeerReviewCriteria();

        foreach ($oldCriteria as $oldCriterion) {
            $newCriterion = new Criterion();
            $newCriterion->setInstruction($oldCriterion->getInstruction());

            $newDropzone->addCriterion($newCriterion);
        }
        $em->persist($newDropzone);

        $event->setCopy($newDropzone);
        $event->stopPropagation();
    }
}
