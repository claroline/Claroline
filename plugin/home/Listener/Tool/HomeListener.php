<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Listener\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Home tool.
 */
class HomeListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        SerializerProvider $serializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->authorization = $authorization;
    }

    /**
     * Displays home on Desktop.
     */
    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $adminTabs = $this->finder->search(HomeTab::class, [
            'filters' => ['context' => HomeTab::TYPE_ADMIN_DESKTOP],
        ]);

        $userTabs = $this->finder->search(HomeTab::class, [
            'filters' => ['context' => HomeTab::TYPE_DESKTOP],
        ]);

        // generate the final list of tabs
        $orderedTabs = array_merge(array_values($adminTabs['data']), array_values($userTabs['data']));

        // we rewrite tab position because an admin and a user tab may have the same position
        foreach ($orderedTabs as $index => &$tab) {
            $tab['position'] = $index;
        }

        $event->setData([
            'tabs' => $orderedTabs,
        ]);
        $event->stopPropagation();
    }

    /**
     * Displays home on Workspace.
     */
    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $workspace = $event->getWorkspace();

        $tabs = $this->finder->search(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_WORKSPACE,
                'workspace' => $workspace->getUuid(),
            ],
        ]);

        $event->setData([
            'tabs' => $tabs['data'],
        ]);
        $event->stopPropagation();
    }

    /**
     * Displays home administration tool.
     */
    public function onDisplayAdministration(OpenToolEvent $event)
    {
        $tabs = $this->finder->search(
            HomeTab::class,
            ['filters' => ['context' => HomeTab::TYPE_ADMIN]]
        );

        $event->setData([
            'administration' => true,
            'tabs' => $tabs['data'],
        ]);
        $event->stopPropagation();
    }
}
