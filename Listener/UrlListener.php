<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Form\UrlType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Service
 */
class UrlListener
{
    private $container;
    private $formFactory;
    private $httpKernel;
    private $om;
    private $request;
    private $router;
    private $securityContext;
    private $templating;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "container"          = @DI\Inject("service_container"),
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "httpKernel"         = @DI\Inject("http_kernel"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "router"             = @DI\Inject("router"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "templating"         = @DI\Inject("templating"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        SecurityContextInterface $securityContext,
        TwigEngine $templating,
        Translator $translator
    ){
        $this->container = $container;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("create_form_hevinci_url")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(
            new UrlType(), new Url()
        );
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', array(
                'form' => $form->createView(),
                'resourceType' => 'hevinci_url'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_hevinci_url")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(
            new UrlType(), new Url()
        );
        $form->handleRequest($this->request);

        if($form->isValid())
        {

            $url = $form->getData();

            $event->setResources(array($url));
            $event->stopPropagation();
            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', array(
                'form' => $form->createView(),
                'resourceType' => $event->getResourceType()
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_hevinci_url")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        if(!$this->request)
            throw new NoHttpRequestException();

        $url = $event->getResource();

        $event->setResponse(new RedirectResponse($url->getUrl()));
    }

    /**
     * @DI\Observe("delete_hevinci_url")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_hevinci_url")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $copy = new Url();
        $copy->setName($resource->getName());
        $copy->setUrl($resource->getUrl());

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_hevinci_url")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $event->setItem($event->getResource()->getUrl());
        $event->stopPropagation();
    }
}