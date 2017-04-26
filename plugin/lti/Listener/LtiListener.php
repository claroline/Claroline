<?php

namespace UJM\LtiBundle\Listener;

use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use UJM\LtiBundle\Entity\LtiResource;
use UJM\LtiBundle\Form\LtiResourceType;

/**
 * @Service()
 */
class LtiListener
{
    private $formFactory;
    private $request;
    private $httpKernel;
    private $container;

    /**
     * @InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "request"    = @Inject("request_stack"),
     *     "httpKernel" = @Inject("http_kernel"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(FormFactory $formFactory, RequestStack $request, HttpKernelInterface $httpKernel, ContainerInterface $container)
    {
        $this->formFactory = $formFactory;
        $this->request = $request->getMasterRequest();
        $this->httpKernel = $httpKernel;
        $this->container = $container;
    }

    /**
     * @Observe("administration_tool_LTI")
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(OpenAdministrationToolEvent $event)
    {
        $params = [];
        $params['_controller'] = 'UJMLtiBundle:Lti:app';
        $this->redirect($params, $event);
    }

    private function redirect($params, $event)
    {
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * Displays a form to create an LTI link.
     *
     * @DI\Observe("create_form_ujm_lti_resource")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        /** @var FormInterface $form */
        $form = $this->container->get('form.factory')->create(new LtiResourceType());

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', [
                'resourceType' => 'ujm_lti_resource',
                'form' => $form->createView(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * Creates a new link LTI app.
     *
     * @DI\Observe("create_ujm_lti_resource")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new LtiResourceType(), new LtiResource());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $ltiResource = $form->getData();
            $em->persist($ltiResource);
            $event->setPublished(true);
            $event->setResources([$ltiResource]);

            return;
        }
        $content = $this->container->get('templating')->render(
               'ClarolineCoreBundle:Resource:createForm.html.twig', [
                   'resourceType' => 'ujm_lti',
                   'form' => $form->createView(),
               ]
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_ujm_lti_resource")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $params['_controller'] = 'UJMLtiBundle:LtiWs:open_app';
        $params['resource'] = $event->getResource();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_ujm_lti_resource")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }
}
