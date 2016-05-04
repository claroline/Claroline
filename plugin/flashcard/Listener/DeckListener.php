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
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\FlashCardBundle\Entity\Deck;
use Claroline\FlashCardBundle\Manager\DeckManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @DI\Service("claroline.flashcard.deck_listener")
 */
class DeckListener
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
     *     "manager"    = @DI\Inject("claroline.flashcard.deck_manager"),
     *     "handler"    = @DI\Inject("claroline.form_handler"),
     *     "context"    = @DI\Inject("security.context")
     * })
     *
     * @param RequestStack        $stack
     * @param HttpKernelInterface $kernel
     * @param DeckManager         $manager
     * @param FormHandler         $handler
     * @param SecurityContext     $context
     */
    public function __construct(
        RequestStack $stack,
        HttpKernelInterface $kernel,
        DeckManager $manager,
        FormHandler $handler,
        SecurityContext $context
    ) {
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
        $view = $this->formHandler->getView('claroline_form_deck');
        $event->setResponseContent($this->manager->getDeckFormContent($view));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_flashcard")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        if ($this->formHandler->isValid('claroline_form_deck', $this->request,
            new Deck())) {
            $event->setResources([$this->manager->create($this->formHandler->getData())]);
        } else {
            $view = $this->formHandler->getView();
            $event->setErrorFormContent($this->manager->getResultFormContent($view));
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_flashcard")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $subRequest = $this->request->duplicate([], null, [
            '_controller' => 'ClarolineFlashCardBundle:Deck:deck',
            'id' => $event->getResource()->getId(),
        ]);
        $event->setResponse($this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_flashcard")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->manager->delete($event->getResource());
        $event->stopPropagation();
    }
}
