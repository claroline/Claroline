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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Workspaces tool.
 */
class WorkspacesListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * HomeListener constructor.
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param FinderProvider                $finder
     * @param SerializerProvider            $serializer
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        SerializerProvider $serializer,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->authorization = $authorization;
    }

    /**
     * Displays workspaces on Desktop.
     *
     * @param OpenToolEvent $event
     */
    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $event->setData([
            'creatable' => $this->authorization->isGranted('CREATE', new Workspace()),
        ]);

        $event->stopPropagation();
    }
}
