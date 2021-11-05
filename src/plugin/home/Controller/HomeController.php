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
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LockManager;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;
use Claroline\HomeBundle\Serializer\HomeTabSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/home")
 */
class HomeController
{
    use RequestDecoderTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
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
    /** @var HomeManager */
    private $manager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        Crud $crud,
        LockManager $lockManager,
        HomeTabSerializer $serializer,
        HomeManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->om = $om;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->lockManager = $lockManager;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Get the platform home data.
     *
     * @Route("/", name="apiv2_home", methods={"GET"})
     */
    public function homeAction(): JsonResponse
    {
        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN') || $this->authorization->isGranted('ROLE_HOME_MANAGER');

        return new JsonResponse([
            'tabs' => $this->manager->getHomeTabs(),
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
                'user' => $this->tokenStorage->getToken()->getUser()->getUuid(),
            ] : [
                $context => $contextId,
            ]);

        $this->om->startFlushSuite();

        $ids = [];
        $updated = [];
        foreach ($tabs as $tab) {
            // do not update tabs set by the administration tool
            if (HomeTab::TYPE_ADMIN_DESKTOP !== $tab['context']) {
                $new = true;
                if (isset($tab['id'])) {
                    foreach ($installedTabs as $installedTab) {
                        if ($installedTab->getUuid() === $tab['id']) {
                            $new = false;
                            break;
                        }
                    }
                }

                if ($new) {
                    $entity = $this->crud->create(HomeTab::class, $tab, [$context, Crud::THROW_EXCEPTION]);
                } else {
                    $entity = $this->crud->update(HomeTab::class, $tab, [$context, Crud::THROW_EXCEPTION]);
                }
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
        $installedTabs = $this->finder->fetch(HomeTab::class, [
            'context' => 'desktop' === $context ? HomeTab::TYPE_ADMIN_DESKTOP : HomeTab::TYPE_ADMIN,
        ]);

        $this->om->startFlushSuite();

        $ids = [];
        $updated = [];
        foreach ($tabs as $tab) {
            $new = true;
            if (isset($tab['id'])) {
                foreach ($installedTabs as $installedTab) {
                    if ($installedTab->getUuid() === $tab['id']) {
                        $new = false;
                        break;
                    }
                }
            }
            if ($new) {
                $entity = $this->crud->create(HomeTab::class, $tab, [$context, Crud::THROW_EXCEPTION]);
            } else {
                $entity = $this->crud->update(HomeTab::class, $tab, [$context, Crud::THROW_EXCEPTION]);
            }

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
        $this->checkDesktopPermissions('OPEN');

        $user = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        return new JsonResponse(
            $this->manager->getDesktopTabs($user)
        );
    }

    /**
     * @Route("/admin/home/tabs/fetch", name="apiv2_home_admin_fetch", methods={"GET"})
     */
    public function adminTabsFetchAction(): JsonResponse
    {
        $this->checkDesktopPermissions('ADMINISTRATE');

        return new JsonResponse(
            $this->manager->getCommonDesktopTabs()
        );
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

    private function checkDesktopPermissions(string $perm)
    {
        $homeTool = $this->om->getRepository(OrderedTool::class)->findOneByNameAndDesktop('home');
        if (!$homeTool || !$this->authorization->isGranted($perm, $homeTool)) {
            throw new AccessDeniedException();
        }
    }
}
