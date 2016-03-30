<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Listener;

use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\FlashCardBundle\Entity\FlashCard;
use Claroline\FlashCardBundle\Manager\FlashCardManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @DI\Service("claroline.flashcard.flashcard_listener")
 */
class FlashCardListener
{
    private $request;
    private $kernel;
    private $manager;
    private $formHandler;
    private $security;

    /**
     * @DI\InjectParams({
     *     "stack"      = @DI\Inject("request_stack"),
     *     "kernel"     = @DI\Inject("http_kernel"),
     *     "manager"    = @DI\Inject("claroline.flashcard.flashcard_manager"),
     *     "handler"    = @DI\Inject("claroline.form_handler"),
     *     "context"    = @DI\Inject("security.context")
     * })
     *
     * @param RequestStack          $stack
     * @param HttpKernelInterface   $kernel
     * @param FlashCardManager      $manager
     * @param FormHandler           $handler
     * @param SecurityContext       $context
     */
    public function __construct(
        RequestStack $stack,
        HttpKernelInterface $kernel,
        FlashCardManager $manager,
        FormHandler $handler,
        SecurityContext $context
    )
    {
        $this->request = $stack->getCurrentRequest();
        $this->kernel = $kernel;
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->security = $context;
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
     * @DI\Observe("create_form_claroline_flashcard")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $view = $this->formHandler->getView('claroline_form_flashcard');
        $event->setResponseContent($this->manager->getFlashCardFormContent($view));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_flashcard")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        if ($this->formHandler->isValid('claroline_form_flashcard', $this->request, 
            new FlashCard())) {
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
            'id' => $event->getResource()->getId()
        ]);
        $event->setResponse($this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_delete")
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
        $user = $this->security->getToken() ?
            $this->security->getToken()->getUser() :
            null;

        if (!$user) {
            throw new \LogicException('Result widget needs an authenticated user');
        }

        $workspace = $event->getInstance()->getWorkspace();

        if (!$this->security->isGranted('OPEN', $workspace)) {
            throw new AccessDeniedException();
        }

        $content = $this->manager->getWidgetContent($workspace, $user);
        $event->setContent($content);
        $event->stopPropagation();
    }
}
