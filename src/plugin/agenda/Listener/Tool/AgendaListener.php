<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Listener\Tool;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        // It would be better to directly handle it in the ui TOOL_LOAD action,
        // but for now I can't access the current user id. It will be possible when desktop context will contain the current user data
        $event->setData([
            'plannings' => $user instanceof User ? [
                ['id' => $user->getUuid(), 'name' => $this->translator->trans('my_agenda', [], 'agenda'), 'locked' => true],
            ] : [],
        ]);
        $event->stopPropagation();
    }

    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $workspace = $event->getWorkspace();

        $event->setData([
            'plannings' => [
                ['id' => $workspace->getUuid(), 'name' => $workspace->getName(), 'locked' => true],
            ],
        ]);
        $event->stopPropagation();
    }
}
