<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Listener;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ChatBundle\Entity\ChatRoom;
use Claroline\ChatBundle\Form\ChatRoomType;
use Claroline\ChatBundle\Manager\ChatManager;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class ChatRoomListener
{
    private $chatManager;
    private $formFactory;
    private $httpKernel;
    private $om;
    private $platformConfigHandler;
    private $request;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "chatManager"           = @DI\Inject("claroline.manager.chat_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "httpKernel"            = @DI\Inject("http_kernel"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "templating"            = @DI\Inject("templating")
     * })
     */
    public function __construct(
        ChatManager $chatManager,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        TwigEngine $templating
    ) {
        $this->chatManager = $chatManager;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("create_form_claroline_chat_room")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreationForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(
            new ChatRoomType($this->platformConfigHandler), new ChatRoom()
        );
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_chat_room',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_chat_room")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(
            new ChatRoomType($this->platformConfigHandler), new ChatRoom()
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $chatRoom = $form->getData();
            $this->om->persist($chatRoom);
            $event->setResources([$chatRoom]);
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_chat_room',
            ]
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_chat_room")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineChatBundle:Chat:chatRoomOpen';
        $params['chatRoom'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_chat_room")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $chatRoom = $event->getResource();
        $copy = $this->chatManager->copyChatRoom($chatRoom);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_chat_room")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }
}
