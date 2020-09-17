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
use Claroline\CoreBundle\Manager\LockManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/home")
 */
class HomeController extends AbstractApiController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
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
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param FinderProvider                $finder
     * @param Crud                          $crud
     * @param LockManager                   $lockManager
     * @param HomeTabSerializer             $serializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        Crud $crud,
        LockManager $lockManager,
        HomeTabSerializer $serializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->lockManager = $lockManager;
        $this->serializer = $serializer;
    }

    /**
     * Get the platform home data.
     *
     * @Route("/", name="apiv2_home", options={"method_prefix"=false})
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function homeAction()
    {
        $tabs = $this->finder->search(HomeTab::class, [
            'filters' => ['type' => HomeTab::TYPE_HOME],
        ]);

        return new JsonResponse([
            'editable' => $this->authorization->isGranted('ROLE_ADMIN') || $this->authorization->isGranted('ROLE_HOME_MANAGER'),
            'administration' => false,
            'tabs' => $tabs['data'],
        ]);
    }

    /**
     * @Route("/{context}/{contextId}", name="apiv2_home_update", options={"method_prefix"=false})
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
        $updated = [];

        foreach ($tabs as $tab) {
            // do not update tabs set by the administration tool
            if (HomeTab::TYPE_ADMIN_DESKTOP !== $tab['type']) {
                $updated[] = $this->crud->update(HomeTab::class, $tab, [$context]);
                $ids[] = $tab['id']; // will be used to determine deleted tabs
            } else {
                $updated[] = $this->om->getObject($tab, HomeTab::class);
            }
        }

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = HomeTab::TYPE_HOME === $context ?
            $this->finder->fetch(HomeTab::class, ['type' => HomeTab::TYPE_HOME]) :
            $this->finder->fetch(HomeTab::class, 'desktop' === $context ? [
                'type' => HomeTab::TYPE_DESKTOP,
            ] : [
                $context => $contextId,
            ]);

        // do not delete tabs set by the administration tool
        $installedTabs = array_filter($installedTabs, function (HomeTab $tab) {
            return HomeTab::TYPE_ADMIN_DESKTOP !== $tab->getType();
        });

        $this->cleanDatabase($installedTabs, $ids);

        return new JsonResponse(array_values(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated)));
    }

    /**
     * @Route("/admin/{context}/{contextId}", name="apiv2_home_admin", options={"method_prefix"=false})
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
        $updated = [];

        foreach ($tabs as $tab) {
            $updated[] = $this->crud->update(HomeTab::class, $tab, [$context]);
            $ids[] = $tab['id']; // will be used to determine deleted tabs
        }

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = $this->finder->fetch(
            HomeTab::class,
            ['type' => 'desktop' === $context ? HomeTab::TYPE_ADMIN_DESKTOP : HomeTab::TYPE_ADMIN]
        );

        $this->cleanDatabase($installedTabs, $ids);

        return new JsonResponse(array_values(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated)));
    }

    /**
     * @Route(
     *     "/home/tabs/fetch",
     *     name="apiv2_home_user_fetch",
     *     options={"method_prefix"=false}
     * )
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function userTabsFetchAction()
    {
        $adminTabs = $this->finder->search(HomeTab::class, [
            'filters' => ['type' => HomeTab::TYPE_ADMIN_DESKTOP],
        ]);

        $userTabs = $this->finder->search(HomeTab::class, [
            'filters' => ['type' => HomeTab::TYPE_DESKTOP],
        ]);

        // generate the final list of tabs
        $orderedTabs = array_merge(array_values($adminTabs['data']), array_values($userTabs['data']));

        // we rewrite tab position because an admin and a user tab may have the same position
        foreach ($orderedTabs as $index => &$tab) {
            $tab['position'] = $index;
        }

        return new JsonResponse($orderedTabs);
    }

    /**
     * @Route(
     *     "/admin/home/tabs/fetch",
     *     name="apiv2_home_admin_fetch",
     *     options={"method_prefix"=false}
     * )
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function adminTabsFetchAction()
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $tabs = $this->finder->search(HomeTab::class, ['filters' => ['type' => HomeTab::TYPE_ADMIN_DESKTOP]]);
        $tabs = array_filter($tabs['data'], function ($data) {
            return $data !== [];
        });
        $orderedTabs = [];

        foreach ($tabs as $tab) {
            $orderedTabs[$tab['position']] = $tab;
        }
        ksort($orderedTabs);

        return new JsonResponse(array_values($orderedTabs));
    }

    private function cleanDatabase(array $installedTabs, array $ids)
    {
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
