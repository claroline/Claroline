<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Listener;

use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Manager\ResultManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.result.result_listener")
 */
class ResultListener
{
    private $request;
    private $kernel;
    private $manager;
    private $formHandler;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "stack"        = @DI\Inject("request_stack"),
     *     "kernel"       = @DI\Inject("http_kernel"),
     *     "manager"      = @DI\Inject("claroline.result.result_manager"),
     *     "handler"      = @DI\Inject("claroline.form_handler"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param RequestStack          $stack
     * @param HttpKernelInterface   $kernel
     * @param ResultManager         $manager
     * @param FormHandler           $handler
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        RequestStack $stack,
        HttpKernelInterface $kernel,
        ResultManager $manager,
        FormHandler $handler,
        TokenStorageInterface $tokenStorage
    ) {
        $this->request = $stack->getCurrentRequest();
        $this->kernel = $kernel;
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Test purpose only.
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @DI\Observe("create_form_claroline_result")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $view = $this->formHandler->getView('claroline_form_result');
        $event->setResponseContent($this->manager->getResultFormContent($view));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_result")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        if ($this->formHandler->isValid('claroline_form_result', $this->request, new Result())) {
            $event->setResources([$this->manager->create($this->formHandler->getData())]);
        } else {
            $view = $this->formHandler->getView();
            $event->setErrorFormContent($this->manager->getResultFormContent($view));
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_result")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $subRequest = $this->request->duplicate([], null, [
            '_controller' => 'ClarolineResultBundle:Result:result',
            'id' => $event->getResource()->getId(),
        ]);
        $event->setResponse($this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_result")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->manager->delete($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_claroline_result")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplayWidget(DisplayWidgetEvent $event)
    {
        $loggedUser = $this->tokenStorage->getToken()->getUser();
        $user = $loggedUser !== 'anon.' ? $loggedUser : null;
        $workspace = $event->getInstance()->getWorkspace();
        $content = $this->manager->getWidgetContent($workspace, $user);
        $event->setContent($content);
        $event->stopPropagation();
    }
}
