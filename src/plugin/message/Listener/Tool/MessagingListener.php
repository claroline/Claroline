<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Listener\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MessagingListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Crud */
    private $crud;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Crud $crud
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->crud = $crud;
    }

    /**
     * Displays messaging on Desktop.
     */
    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $mailNotified = false;
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if ($currentUser instanceof User) {
            $mailNotified = $currentUser->isMailNotified();
        }

        $event->setData([
            'mailNotified' => $mailNotified,
        ]);
        $event->stopPropagation();
    }

    /**
     * Configures messaging on Desktop.
     */
    public function onConfigureDesktop(ConfigureToolEvent $event)
    {
        $parameters = $event->getParameters();

        $currentUser = $this->tokenStorage->getToken()->getUser();
        if (!($currentUser instanceof User)) {
            $event->setData([]);
            $event->stopPropagation();

            return;
        }

        $mailNotified = false;
        if (isset($parameters['mailNotified'])) {
            $mailNotified = $parameters['mailNotified'];
        }

        $this->crud->update($this->tokenStorage->getToken()->getUser(), [
            'meta' => ['mailNotified' => $mailNotified],
        ], [Crud::NO_PERMISSIONS, Crud::THROW_EXCEPTION]);

        $event->setData([
            'mailNotified' => $mailNotified,
        ]);
        $event->stopPropagation();
    }
}
