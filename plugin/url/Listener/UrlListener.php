<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Form\UrlChangeType;
use HeVinci\UrlBundle\Form\UrlType;
use HeVinci\UrlBundle\Manager\UrlManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service
 */
class UrlListener
{
    private $formFactory;
    private $om;
    private $request;
    private $templating;
    private $manager;
    private $urlManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "templating"         = @DI\Inject("templating"),
     *     "manager"            = @DI\Inject("claroline.manager.resource_manager"),
     *     "urlManager"         = @DI\Inject("hevinci_url.manager.url")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        TwigEngine $templating,
        ResourceManager $manager,
        UrlManager $urlManager
    ) {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->manager = $manager;
        $this->urlManager = $urlManager;
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
            'ClarolineCoreBundle:Resource:createForm.html.twig', [
                'form' => $form->createView(),
                'resourceType' => 'hevinci_url',
            ]
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

        if ($form->isValid()) {
            $url = $form->getData();
            $this->urlManager->setUrl($url);

            $event->setResources([$url]);
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', [
                'form' => $form->createView(),
                'resourceType' => $event->getResourceType(),
            ]
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
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $url = $event->getResource();
        $isIframe = $event->isIframe();

        if ($url->getInternalUrl()) {
            $event->setResponse(new RedirectResponse($this->request->getSchemeAndHttpHost().$this->request->getScriptName().$url->getUrl()));

            return;
        } else {
            if ($isIframe) {
                $headers = get_headers($url->getUrl(), 1);
                if (isset($headers['X-Frame-Options']) && $headers['X-Frame-Options'] === 'SAMEORIGIN') {
                    $href = "<a href='{$url->getUrl()}'>{$url->getUrl()}</a>";
                    $response = new Response($href);
                    $event->setResponse($response);

                    return;
                }
            }
        }

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
        $copy->setInternalUrl($resource->getInternalUrl());

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("change_url_menu_hevinci_url")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onChangeAction(CustomActionResourceEvent $event)
    {
        $resource = get_class($event->getResource()) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut' ?
            $this->manager->getResourceFromShortcut($event->getResource()->getResourceNode()) :
            $event->getResource();
        $form = $this->formFactory->create(new UrlChangeType(), $resource);
        $form->handleRequest($this->request);

        $content = $this->templating->render('HeVinciUrlBundle:Url:form.html.twig', [
            'form' => $form->createView(),
            'node' => $resource->getResourceNode()->getId(),
        ]);

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
