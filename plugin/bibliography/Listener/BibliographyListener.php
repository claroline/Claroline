<?php

namespace Icap\BibliographyBundle\Listener;

use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Icap\BibliographyBundle\Entity\BookReference;
use Icap\BibliographyBundle\Form\BookReferenceType;
use Icap\BibliographyBundle\Manager\BookReferenceManager;
use Icap\BibliographyBundle\Repository\BookReferenceConfigurationRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class BibliographyListener
{
    private $container;
    private $httpKernel;
    private $request;
    protected $manager;
    protected $configRepository;

    /**
     * @DI\InjectParams({
     *     "container"        = @DI\Inject("service_container"),
     *     "httpKernel"       = @DI\Inject("http_kernel"),
     *     "requestStack"     = @DI\Inject("request_stack"),
     *     "manager"          = @DI\Inject("icap.bookReference.manager"),
     *     "configRepository" = @DI\Inject("icap_bibliography.repository.book_reference_configuration")
     * })
     *
     * @param ContainerInterface                   $container
     * @param HttpKernelInterface                  $httpKernel
     * @param RequestStack                         $requestStack
     * @param BookReferenceManager                 $manager
     * @param BookReferenceConfigurationRepository $configRepository
     */
    public function __construct(
        ContainerInterface $container,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        BookReferenceManager $manager,
        BookReferenceConfigurationRepository $configRepository
    ) {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
        $this->configRepository = $configRepository;
    }

    /**
     * @DI\Observe("create_form_icap_bibliography")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new BookReferenceType(), new BookReference());
        $content = $this->container->get('templating')->render(
            'IcapBibliographyBundle:BookReference:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'icap_bibliography',
                'isApiConfigured' => $this->configRepository->isApiConfigured(),
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_icap_bibliography")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new BookReferenceType(), new BookReference());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var BookReference $bookResource */
            $bookResource = $form->getData();

            $bookReferenceInWorkspace = $this->manager->bookExistsInWorkspace($bookResource->getIsbn(), $event->getParent()->getWorkspace());

            if ($bookReferenceInWorkspace !== null) {
                // Book already exists, create a link instead of a new resource

                $resourceShortcut = new ResourceShortcut();
                $resourceShortcut->setTarget($bookReferenceInWorkspace->getResourceNode());
                $resourceShortcut->setName($bookReferenceInWorkspace->getResourceNode()->getName());

                $event->setResourceType('resource_shortcut');
                $event->setParent($bookReferenceInWorkspace->getResourceNode());
                $event->setResources([$resourceShortcut]);
            } else {
                // Create book as a new resource
                $event->setResources([$bookResource]);
            }
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'icap_bibliography',
                ]
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_icap_bibliography")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $bookReference = $event->getResource();
        $content = $this->container->get('templating')->render(
            'IcapBibliographyBundle:BookReference:open.html.twig',
            [
                '_resource' => $bookReference,
            ]
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_icap_bibliography")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $em = $this->container->get('claroline.persistence.object_manager');

        /** @var BookReference $old */
        $old = $event->getResource();
        $new = new BookReference();

        $new->setAuthor($old->getAuthor());
        $new->setDescription($old->getDescription());
        $new->setAbstract($old->getAbstract());
        $new->setIsbn($old->getIsbn());
        $new->setPublisher($old->getPublisher());
        $new->setPrinter($old->getPrinter());
        $new->setPublicationYear($old->getPublicationYear());
        $new->setLanguage($old->getLanguage());
        $new->setPageCount($old->getPageCount());
        $new->setUrl($old->getUrl());
        $new->setCoverUrl($old->getCoverUrl());

        $em->persist($new);
        $em->flush();

        $event->setCopy($new);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_icap_bibliography")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('claroline.persistence.object_manager');
        $em->remove($event->getResource());
        $em->flush();
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("change_bookreference_menu_icap_bibliography")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onChangeAction(CustomActionResourceEvent $event)
    {
        $resource = get_class($event->getResource()) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut' ?
            $this->manager->getResourceFromShortcut($event->getResource()->getResourceNode()) :
            $event->getResource();
        $resource->setName($event->getResource()->getResourceNode()->getName());
        $form = $this->container->get('form.factory')->create(new BookReferenceType(), $resource);
        $form->handleRequest($this->request);

        $content = $this->container->get('templating')->render('IcapBibliographyBundle:BookReference:editForm.html.twig', [
            'form' => $form->createView(),
            'node' => $resource->getResourceNode()->getId(),
        ]);

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_bibliographybundle")
     *
     * @param PluginOptionsEvent $event
     */
    public function onConfig(PluginOptionsEvent $event)
    {
        $params = [];
        $params['_controller'] = 'IcapBibliographyBundle:BookReference:pluginConfigureForm';
        $subRequest = $this->container->get('request')->duplicate([], null, $params);
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
