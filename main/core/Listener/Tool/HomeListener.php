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
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Home tool.
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

    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $om;

    /**
     * HomeListener constructor.
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param TwigEngine                    $templating
     * @param FinderProvider                $finder
     * @param SerializerProvider            $serializer
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        TwigEngine $templating,
        FinderProvider $finder,
        SerializerProvider $serializer,
        TranslatorInterface $translator,
        ObjectManager $om
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->authorization = $authorization;
        $this->om = $om;
        $this->translator = $translator;
    }

    /**
     * Displays home on Desktop.
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');

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

        $roles = $isAdmin ?
            $this->finder->search('Claroline\CoreBundle\Entity\Role', ['filters' => ['type' => Role::PLATFORM_ROLE]]) :
            [];

        $event->setData([
            'editable' => true,
            'tabs' => $orderedTabs,
            'roles' => $isAdmin ? $roles['data'] : [],
            'desktopAdmin' => $isAdmin,
        ]);
        $event->stopPropagation();
    }

    /**
     * Displays home on Workspace.
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

        $tabs = array_values($orderedTabs);

        if (0 === count($orderedTabs)) {
            $defaultTab = new HomeTab();
            $defaultTab->setType(HomeTab::TYPE_WORKSPACE);
            $defaultTab->setWorkspace($workspace);
            $this->om->persist($defaultTab);
            $defaultHomeTabConfig = new HomeTabConfig();
            $defaultHomeTabConfig->setHomeTab($defaultTab);
            $defaultHomeTabConfig->setName($this->translator->trans('home', [], 'platform'));
            $defaultHomeTabConfig->setLongTitle($this->translator->trans('home', [], 'platform'));
            $defaultHomeTabConfig->setLocked(true);
            $defaultHomeTabConfig->setTabOrder(0);
            $this->om->persist($defaultHomeTabConfig);
            $this->om->flush();
            $orderedTabs[] = $this->serializer->serialize($defaultTab);
        }

        $event->setData([
            'editable' => $this->authorization->isGranted(['home', 'edit'], $workspace),
            'tabs' => $tabs,
            'roles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $workspace->getRoles()->toArray()),
        ]);
        $event->stopPropagation();
    }
}
