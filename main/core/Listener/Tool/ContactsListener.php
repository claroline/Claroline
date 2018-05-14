<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Manager\ContactManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Contacts tool.
 *
 * @DI\Service()
 */
class ContactsListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var TwigEngine */
    private $templating;

    /** @var ContactManager */
    private $contactManager;

    /**
     * ContactsListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "templating"     = @DI\Inject("templating"),
     *     "contactManager" = @DI\Inject("claroline.manager.contact_manager")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param TwigEngine            $templating
     * @param ContactManager        $contactManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TwigEngine $templating,
        ContactManager $contactManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->contactManager = $contactManager;
    }

    /**
     * Displays contacts on Desktop.
     *
     * @DI\Observe("open_tool_desktop_my_contacts")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopContactTool(DisplayToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:tool:contacts.html.twig', [
                'options' => $this->contactManager->getUserOptions(
                    $this->tokenStorage->getToken()->getUser()
                ),
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
