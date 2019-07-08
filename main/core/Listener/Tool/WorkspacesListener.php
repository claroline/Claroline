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
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Workspaces tool.
 *
 * @DI\Service()
 */
class WorkspacesListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TwigEngine */
    private $templating;

    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * HomeListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "templating"    = @DI\Inject("templating"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param TwigEngine                    $templating
     * @param FinderProvider                $finder
     * @param SerializerProvider            $serializer
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TwigEngine $templating,
        FinderProvider $finder,
        SerializerProvider $serializer,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->authorization = $authorization;
    }

    /**
     * Displays workspaces on Desktop.
     *
     * @DI\Observe("open_tool_desktop_workspaces")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->setData([
            'creatable' => $this->authorization->isGranted('CREATE', new Workspace()),
        ]);

        $event->stopPropagation();
    }
}
