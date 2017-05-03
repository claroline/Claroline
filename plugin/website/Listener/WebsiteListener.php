<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/4/14
 * Time: 4:02 PM.
 */

namespace Icap\WebsiteBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Form\WebsiteType;
use Icap\WebsiteBundle\Manager\WebsiteManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class WebsiteListener
{
    private $container;
    private $httpKernel;
    private $request;
    private $websiteManager;

    /**
     * @DI\InjectParams({
     *     "container"    = @DI\Inject("service_container"),
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "websiteManager"= @DI\Inject("icap.website.manager")
     * })
     */
    public function __construct(ContainerInterface $container, HttpKernelInterface $httpKernel, RequestStack $requestStack, WebsiteManager $websiteManager)
    {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->websiteManager = $websiteManager;
    }

    /**
     * @DI\Observe("create_form_icap_website")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new WebsiteType(), new Website());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'icap_website',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_icap_website")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new WebsiteType(), new Website());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $website = $form->getData();
            $event->setResources([$website]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'icap_website',
                ]
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_icap_website")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $params['_controller'] = 'IcapWebsiteBundle:Website:view';
        $params['websiteId'] = $event->getResource()->getId();
        $params['view'] = false;
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_icap_website")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->container->get('icap.website.manager')->deleteWebsite($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_icap_website")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $website = $event->getResource();

        $newWebsite = $this->container->get('icap.website.manager')->copyWebsite($website);

        $event->setCopy($newWebsite);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("log")
     *
     * @param LogGenericEvent $event
     */
    public function onRename(LogGenericEvent $event)
    {
        if (
            $event instanceof LogResourceUpdateEvent
            && $event->getResource()->getClass() === 'Icap\WebsiteBundle\Entity\Website'
            && $event->getAction() === 'resource-update_rename'
            && $event->getDetails()['resource']['changeSet']['name'][0] !== $event->getDetails()['resource']['changeSet']['name'][1]
        ) {
            $resourceNode = $event->getResource();
            $newTitle = $event->getDetails()['resource']['changeSet']['name'][1];

            $this->websiteManager->renameRootPageByResourceNode($newTitle, $resourceNode);
        }
    }
}
