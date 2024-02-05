<?php

namespace Claroline\PrivacyBundle\Listener;

use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class PrivacyListener
{
    private PrivacyManager $privacyManager;
    private TranslatorInterface $translator;
    private RoutingHelper $routingHelper;
    private SecurityManager $securityManager;

    public function __construct(
        PrivacyManager $privacyManager,
        TranslatorInterface $translator,
        RoutingHelper $routingHelper,
        SecurityManager $securityManager
    ) {
        $this->privacyManager = $privacyManager;
        $this->translator = $translator;
        $this->routingHelper = $routingHelper;
        $this->securityManager = $securityManager;
    }

    public function getDPOMessages(GenericDataEvent $event): void
    {
        if (!$this->securityManager->isAdmin() || $this->privacyManager->getParameters()->getDpoEmail()) {
            return;
        }

        $editUrl = $this->routingHelper->adminPath('privacy');

        $event->setResponse([
            [
                'id' => 'dpo-email-missing',
                'title' => $this->translator->trans('dpo_email_missing_title', [], 'privacy'),
                'type' => ConnectionMessage::TYPE_ALWAYS,
                'slides' => [[
                    'id' => 'dpo-email-missing-message',
                    'title' => $this->translator->trans('dpo_email_missing_title', [], 'privacy'),
                    'content' => $this->translator->trans('dpo_email_missing_content', ['%link%' => '<a href="'.$editUrl.'" target="_blank"><strong>'.$this->translator->trans('here', [], 'platform').'</strong></a>'], 'privacy'),
                    'order' => 1,
                ]],
            ],
        ]);
    }
}
