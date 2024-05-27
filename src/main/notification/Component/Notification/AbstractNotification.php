<?php

namespace Claroline\NotificationBundle\Component\Notification;

use Claroline\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractNotification implements EventSubscriberInterface, NotificationInterface
{
    private TranslatorInterface $translator;
    private NotificationManager $notificationManager;

    /**
     * @internal only used by DI
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @internal only used by DI
     */
    public function setNotificationManager(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    /**
     * Shortcut to the app Translator.
     * It's equivalent to $this->>getTranslator()->trans(string $message, array $parameters = [], ?string $domain = null).
     */
    protected function trans(string $message, array $parameters = [], ?string $domain = null): string
    {
        return $this->getTranslator()->trans($message, $parameters, $domain);
    }

    protected function notify(string $message, array $users): void
    {
        $this->notificationManager->createNotifications($message, $users);
    }
}
