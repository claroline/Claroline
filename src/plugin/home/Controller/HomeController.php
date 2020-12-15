<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\LockManager;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Serializer\HomeTabSerializer;
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
     * @Route("/", name="apiv2_home", methods={"GET"})
     */
    public function homeAction(): JsonResponse
    {
        $tabs = $this->finder->search(HomeTab::class, [
            'filters' => ['context' => HomeTab::TYPE_HOME],
        ]);

        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN') || $this->authorization->isGranted('ROLE_HOME_MANAGER');

        return new JsonResponse([
            'tabs' => $tabs['data'],
            'data' => [
                // mimic standard tool perms
                'permissions' => [
                    'edit' => $isAdmin,
                    'administrate' => $isAdmin,
                    'delete' => $isAdmin,
                ],
            ],
        ]);
    }

    /**
     * @Route("/{context}/{contextId}", name="apiv2_home_update", methods={"PUT"})
     */
    public function updateAction(Request $request, string $context, string $contextId = null): JsonResponse
    {
        // grab tabs data
        $tabs = $this->decodeRequest($request);

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = HomeTab::TYPE_HOME === $context ?
            $this->finder->fetch(HomeTab::class, ['context' => HomeTab::TYPE_HOME]) :
            $this->finder->fetch(HomeTab::class, 'desktop' === $context ? [
                'context' => HomeTab::TYPE_DESKTOP,
            ] : [
                $context => $contextId,
            ]);

        $this->om->startFlushSuite();

        $ids = [];
        $updated = [];
        foreach ($tabs as $tab) {
            // do not update tabs set by the administration tool
            if (HomeTab::TYPE_ADMIN_DESKTOP !== $tab['context']) {
                $entity = $this->crud->update(HomeTab::class, $tab, [$context, Crud::THROW_EXCEPTION]);
            } else {
                $entity = $this->om->getObject($tab, HomeTab::class);
            }

            if ($entity) {
                $updated[] = $entity;
                $ids = array_merge($ids, [$entity->getUuid()], array_map(function (HomeTab $child) {
                    return $child->getUuid();
                }, $entity->getChildren()->toArray())); // will be used to determine deleted tabs
            }
        }

        $this->cleanDatabase($installedTabs, $ids);

        $this->om->endFlushSuite();

        return new JsonResponse(array_values(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated)));
    }

    /**
     * @Route("/admin/{context}/{contextId}", name="apiv2_home_admin", methods={"PUT"})
     */
    public function adminUpdateAction(Request $request, string $context): JsonResponse
    {
        // grab tabs data
        $tabs = $this->decodeRequest($request);

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = $this->finder->fetch(
            HomeTab::class,
            ['context' => 'desktop' === $context ? HomeTab::TYPE_ADMIN_DESKTOP : HomeTab::TYPE_ADMIN]
        );

        $this->om->startFlushSuite();

        $ids = [];
        $updated = [];
        foreach ($tabs as $tab) {
            $entity = $this->crud->update(HomeTab::class, $tab, [$context]);
            $updated[] = $entity;
            $ids = array_merge($ids, [$entity->getUuid()], array_map(function (HomeTab $child) {
                return $child->getUuid();
            }, $entity->getChildren()->toArray())); // will be used to determine deleted tabs
        }

        $this->cleanDatabase($installedTabs, $ids);

        $this->om->endFlushSuite();

        return new JsonResponse(array_values(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated)));
    }

    /**
     * @Route("/home/tabs/fetch", name="apiv2_home_user_fetch", methods={"GET"})
     */
    public function userTabsFetchAction(): JsonResponse
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

        return new JsonResponse($orderedTabs);
    }

    /**
     * @Route("/admin/home/tabs/fetch", name="apiv2_home_admin_fetch", methods={"GET"})
     */
    public function adminTabsFetchAction(): JsonResponse
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $tabs = $this->finder->search(HomeTab::class, ['filters' => ['context' => HomeTab::TYPE_ADMIN_DESKTOP]]);
        $tabs = array_filter($tabs['data'], function ($data) {
            return !empty($data); // todo : check why this is required
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
            }
        }
    }
}
