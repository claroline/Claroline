<?php

namespace Claroline\HomeBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Manager\LockManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/home_tab")
 */
class HomeTabController extends AbstractCrudController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly LockManager $lockManager,
        private readonly HomeManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public function getName(): string
    {
        return 'home_tab';
    }

    public function getClass(): string
    {
        return HomeTab::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'update', 'list'];
    }

    /**
     * @Route("/open/{id}", name="claro_home_tab_open", methods={"GET"})
     *
     * @EXT\ParamConverter("homeTab", options={"mapping": {"id": "uuid"}})
     */
    public function openAction(HomeTab $homeTab): JsonResponse
    {
        $accessErrors = $this->manager->getRestrictionsErrors($homeTab);
        $isManager = $this->checkPermission('EDIT', $homeTab);
        if (empty($accessErrors) || $isManager) {
            return new JsonResponse([
                'managed' => $isManager,
                'homeTab' => $this->serializer->serialize($homeTab),
                // append access restrictions to the loaded node if any
                // to let the manager knows that other users can not enter the resource
                'accessErrors' => $accessErrors,
            ]);
        }

        return new JsonResponse([
            'managed' => $isManager,
            'homeTab' => $this->serializer->serialize($homeTab, [Options::SERIALIZE_MINIMAL]),
            // append access restrictions to the loaded node if any
            // to let the manager knows that other users can not enter the resource
            'accessErrors' => $accessErrors,
        ], 403);
    }

    /**
     * Submit access code.
     *
     * @Route("/unlock/{id}", name="claro_home_tab_unlock", methods={"POST"})
     *
     * @EXT\ParamConverter("homeTab", options={"mapping": {"id": "uuid"}})
     */
    public function unlockAction(HomeTab $homeTab, Request $request): JsonResponse
    {
        $this->manager->unlock($homeTab, $request);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{context}/{contextId}", name="apiv2_home_update", methods={"PUT"})
     */
    public function updateContextAction(Request $request, string $context, string $contextId = null): JsonResponse
    {
        // grab tabs data
        $tabs = $this->decodeRequest($request);

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = $this->om->getRepository(HomeTab::class)->findBy([
            'contextName' => $context,
            'contextId' => $contextId,
        ]);

        $this->om->startFlushSuite();

        $ids = [];
        $updated = [];
        foreach ($tabs as $tab) {
            $new = true;
            $existingTab = null;
            if (isset($tab['id'])) {
                foreach ($installedTabs as $installedTab) {
                    if ($installedTab->getUuid() === $tab['id']) {
                        $existingTab = $installedTab;
                        $new = false;
                        break;
                    }
                }
            }

            if (empty($existingTab)) {
                $existingTab = new HomeTab();
                $existingTab->setContextName($context);
                $existingTab->setContextId($contextId);
            }

            if ($new) {
                $this->crud->create($existingTab, $tab, [Crud::THROW_EXCEPTION]);
            } else {
                $this->crud->update($existingTab, $tab, [Crud::THROW_EXCEPTION]);
            }

            $updated[] = $existingTab;
            $ids = array_merge($ids, [$existingTab->getUuid()], array_map(function (HomeTab $child) {
                return $child->getUuid();
            }, $existingTab->getChildren()->toArray())); // will be used to determine deleted tabs
        }

        $this->cleanDatabase($installedTabs, $ids);

        $this->om->endFlushSuite();

        return new JsonResponse(array_values(array_map(function (HomeTab $tab) {
            return $this->serializer->serialize($tab);
        }, $updated)));
    }

    private function cleanDatabase(array $installedTabs, array $ids): void
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
