<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Form\UrlType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 */
class UrlListener
{
    private $formFactory;
    private $om;
    private $request;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "templating"         = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        TwigEngine $templating
    ){
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
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

        if ($form->isValid()) {
            $formInterface = $form->getData();
            $url = $formInterface->getUrl();
            $baseUrl = $this->request->getSchemeAndHttpHost() . $this->request->getScriptName();
            $baseUrlEscapeQuote = preg_quote($baseUrl);

            if (preg_match("#$baseUrlEscapeQuote#", $url)) {
                $formInterface->setUrl(substr($url, strlen($baseUrl)));
                $formInterface->setInternalUrl(true);
            }

            $event->setResources(array($formInterface));
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
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $url = $event->getResource();

        if ($url->getInternalUrl()) {
            $event->setResponse(new RedirectResponse($this->request->getSchemeAndHttpHost() . $this->request->getScriptName() . $url->getUrl()));
        } else {
            $event->setResponse(new RedirectResponse($url->getUrl()));
        }
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
}