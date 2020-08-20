<?php

namespace Icap\NotificationBundle\Event\Notification;

use Icap\NotificationBundle\Entity\NotificationViewer;
use Symfony\Contracts\EventDispatcher\Event;

class NotificationCreateDelegateViewEvent extends Event
{
    private $responseContent = '';
    private $notificationView;
    private $systemName = '';
    private $isPopulated = false;
    private $primaryAction = null;
    private $text;

    public function __construct(NotificationViewer $notificationView, $systemName)
    {
        $this->notificationView = $notificationView;
        $this->systemName = $systemName;
    }

    /**
     * Sets the response content (creation form as string).
     *
     * @param string $responseContent
     */
    public function setResponseContent($responseContent)
    {
        $this->isPopulated = true;
        $this->responseContent = $responseContent;
    }

    /**
     * Returns the response content (creation form as string).
     *
     * @return string
     */
    public function getResponseContent()
    {
        return $this->responseContent;
    }

    public function getNotificationView()
    {
        return $this->notificationView;
    }

    public function getSystemName()
    {
        return $this->systemName;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }

    public function setPrimaryAction(array $primaryAction)
    {
        $this->primaryAction = $primaryAction;
    }

    public function getPrimaryAction()
    {
        return $this->primaryAction;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }
}
