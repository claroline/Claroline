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
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Home tool.
 *
 * @DI\Service()
 */
class HomeListener
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
     * Displays home on Desktop.
     *
     * @DI\Observe("open_tool_desktop_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $allTabs = $this->finder->search(HomeTab::class, [
            'filters' => ['user' => $currentUser->getUuid()],
        ]);

        // Order tabs. We want :
        //   - Administration tabs to be at first
        //   - Tabs to be ordered by position
        // For this, we separate administration tabs and user ones, order them by position
        // and then concat all tabs with admin in first (I don't have a easier solution to achieve this)

        $adminTabs = [];
        $userTabs = [];
        foreach ($allTabs['data'] as $tab) {
            if (!empty($tab)) {
                // we use the define position for array keys for easier sort
                if (HomeTab::TYPE_ADMIN_DESKTOP === $tab['type']) {
                    $adminTabs[$tab['position']] = $tab;
                } else {
                    $userTabs[$tab['position']] = $tab;
                }
            }
        }

        // order tabs by position
        ksort($adminTabs);
        ksort($userTabs);

        // generate the final list of tabs
        $orderedTabs = array_merge(array_values($adminTabs), array_values($userTabs));

        // we rewrite tab position because an admin and a user tab may have the same position
        foreach ($orderedTabs as $index => &$tab) {
            $tab['position'] = $index;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:tool:home.html.twig', [
                'editable' => true,
                'context' => [
                    'type' => Widget::CONTEXT_DESKTOP,
                ],
                'tabs' => $orderedTabs,
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * Displays home on Workspace.
     *
     * @DI\Observe("open_tool_workspace_home")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $orderedTabs = [];
        $workspace = $event->getWorkspace();

        $tabs = $this->finder->search(HomeTab::class, [
            'filters' => ['workspace' => $workspace->getUuid()],
        ]);

        // but why ? finder should never give you an empty row
        $tabs = array_filter($tabs['data'], function ($data) {
            return $data !== [];
        });

        foreach ($tabs as $tab) {
            $orderedTabs[$tab['position']] = $tab;
        }
        ksort($orderedTabs);

        $content = $this->templating->render(
            'ClarolineCoreBundle:tool:home.html.twig', [
                'workspace' => $workspace,
                'editable' => $this->authorization->isGranted(['home', 'edit'], $workspace),
                'context' => [
                    'type' => Widget::CONTEXT_WORKSPACE,
                    'data' => $this->serializer->serialize($workspace),
                ],
                'tabs' => array_values($orderedTabs),
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
