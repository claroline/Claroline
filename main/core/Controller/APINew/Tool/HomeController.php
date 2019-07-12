<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Widget\HomeTabSerializer;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Manager\LockManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @EXT\Route("/home")
 */
class HomeController extends AbstractApiController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var TranslatorInterface */
    private $translator;
    /** @var FinderProvider */
    private $finder;
    /** @var Crud */
    private $crud;
    /** @var HomeTabSerializer */
    private $serializer;
    /** @var LockManager */
    private $lockManager;

    /**
     * HomeController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator"    = @DI\Inject("translator"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "lockManager"   = @DI\Inject("claroline.manager.lock_manager"),
     *     "crud"          = @DI\Inject("claroline.api.crud"),
     *     "serializer"    = @DI\Inject("claroline.serializer.home_tab")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param TranslatorInterface           $translator
     * @param FinderProvider                $finder
     * @param Crud                          $crud
     * @param LockManager                   $lockManager
     * @param HomeTabSerializer             $serializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TranslatorInterface $translator,
        FinderProvider $finder,
        Crud $crud,
        LockManager $lockManager,
        HomeTabSerializer $serializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->translator = $translator;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->lockManager = $lockManager;
        $this->serializer = $serializer;
    }

    /**
     * Get the platform home data.
     *
     * @EXT\Route("/", name="apiv2_home", options={"method_prefix"=false})
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function homeAction()
    {
        $tabs = $this->finder->search(
            HomeTab::class,
            ['filters' => ['type' => HomeTab::TYPE_HOME]]
        );

        $tabs = array_filter($tabs['data'], function ($data) {
            return $data !== [];
        });
        $orderedTabs = [];

        foreach ($tabs as $tab) {
            $orderedTabs[$tab['position']] = $tab;
        }
        ksort($orderedTabs);

        if (0 === count($orderedTabs)) {
            $defaultTab = new HomeTab();
            $defaultTab->setType(HomeTab::TYPE_HOME);
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

        return new JsonResponse([
            'editable' => $this->authorization->isGranted('ROLE_ADMIN') ||
                $this->authorization->isGranted('ROLE_HOME_MANAGER'),
            'administration' => false,
            'tabs' => array_values($orderedTabs),
        ]);
    }

    /**
     * @EXT\Route("/{context}/{contextId}", name="apiv2_home_update", options={"method_prefix"=false})
     * @EXT\Method("PUT")
     *
     * @param Request $request
     * @param string  $context
     * @param string  $contextId
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request, $context, $contextId)
    {
        // grab tabs data
        $tabs = $this->decodeRequest($request);

        $ids = [];
        $containerIds = [];
        $instanceIds = [];
        $updated = [];

        foreach ($tabs as $tab) {
            // do not update tabs set by the administration tool
            if (HomeTab::TYPE_ADMIN_DESKTOP !== $tab['type']) {
                $updated[] = $this->crud->update(HomeTab::class, $tab, [$context]);
                $ids[] = $tab['id']; // will be used to determine deleted tabs
            } else {
                $updated[] = $this->om->getObject($tab, HomeTab::class);
            }

            foreach ($tab['widgets'] as $container) {
                $containerIds[] = $container['id'];
                foreach ($container['contents'] as $instance) {
                    $instanceIds[] = $instance['id'];
                }
            }
        }

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = HomeTab::TYPE_HOME === $context ?
            $this->finder->fetch(HomeTab::class, ['type' => HomeTab::TYPE_HOME]) :
            $this->finder->fetch(HomeTab::class, 'desktop' === $context ? [
                'user' => $contextId,
            ] : [
                $context => $contextId,
            ]);

        // do not delete tabs set by the administration tool
        $installedTabs = array_filter($installedTabs, function (HomeTab $tab) {
            return HomeTab::TYPE_ADMIN_DESKTOP !== $tab->getType();
        });

        $this->cleanDatabase($installedTabs, $instanceIds, $containerIds, $ids);

        return new JsonResponse(array_values(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated)));
    }

    /**
     * @EXT\Route("/admin/{context}/{contextId}", name="apiv2_home_admin", options={"method_prefix"=false})
     * @EXT\Method("PUT")
     *
     * @param Request $request
     * @param string  $context
     *
     * @return JsonResponse
     */
    public function adminUpdateAction(Request $request, $context)
    {
        // grab tabs data
        $tabs = $this->decodeRequest($request);

        $ids = [];
        $containerIds = [];
        $instanceIds = [];
        $updated = [];

        foreach ($tabs as $tab) {
            $updated[] = $this->crud->update(HomeTab::class, $tab, [$context]);
            $ids[] = $tab['id']; // will be used to determine deleted tabs

            foreach ($tab['widgets'] as $container) {
                $containerIds[] = $container['id'];
                foreach ($container['contents'] as $instance) {
                    $instanceIds[] = $instance['id'];
                }
            }
        }

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = $this->finder->fetch(HomeTab::class, ['type' => HomeTab::TYPE_ADMIN_DESKTOP]);

        $this->cleanDatabase($installedTabs, $instanceIds, $containerIds, $ids);

        return new JsonResponse(array_values(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated)));
    }

    private function cleanDatabase(array $installedTabs, array $instanceIds, array $containerIds, array $ids)
    {
        //ready to remove instances aswell. We must do it here or we might remove them too early in the serializer
        //ie: if we move them from the top container to the bottom one
        $installedInstances = [];
        $installedContainers = [];

        foreach ($installedTabs as $installedTab) {
            $installedInstances = array_merge($installedInstances, $this->finder->fetch(
              WidgetInstance::class, ['homeTab' => $installedTab->getUuid()]
          ));
            $installedContainers = array_merge($installedContainers, $this->finder->fetch(
              WidgetContainer::class, ['homeTab' => $installedTab->getUuid()]
          ));
        }

        foreach ($installedInstances as $instance) {
            if (!in_array($instance->getUuid(), $instanceIds)) {
                $this->crud->delete($instance);
            } else {
                $this->om->refresh($instance);
            }
        }

        foreach ($installedContainers as $container) {
            if (!in_array($container->getUuid(), $containerIds)) {
                $this->crud->delete($container);
            } else {
                $this->om->refresh($container);
            }
        }

        foreach ($installedTabs as $installedTab) {
            $this->lockManager->unlock(HomeTab::class, $installedTab->getUuid());

            if (!in_array($installedTab->getUuid(), $ids)) {
                // the tab no longer exist we can remove it
                $this->crud->delete($installedTab);
            } else {
                $this->om->refresh($installedTab);
            }
        }
    }
}
