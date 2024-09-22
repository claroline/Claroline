<?php

namespace Claroline\HomeBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/home_tab', name: 'apiv2_home_tab_')]
class HomeTabController extends AbstractCrudController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly HomeManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'home_tab';
    }

    public static function getClass(): string
    {
        return HomeTab::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'update', 'list'];
    }

    /**
     * @EXT\ParamConverter("homeTab", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/open/{id}', name: 'open', methods: ['GET'])]
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
     *
     * @EXT\ParamConverter("homeTab", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/unlock/{id}', name: 'unlock', methods: ['POST'])]
    public function unlockAction(HomeTab $homeTab, Request $request): JsonResponse
    {
        $this->manager->unlock($homeTab, $request);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{context}/{contextId}', name: 'update', methods: ['PUT'])]
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
            if (!in_array($installedTab->getUuid(), $ids)) {
                // the tab no longer exist we can remove it
                $this->crud->delete($installedTab);
            }
        }
    }
}
