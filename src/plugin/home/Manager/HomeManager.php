<?php

namespace Claroline\HomeBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HomeManager
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->serializer = $serializer;
    }

    /**
     * Get platform home tabs.
     */
    public function getHomeTabs(): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_HOME,
            ],
        ]);

        return $this->formatTabs($tabs['data']);
    }

    /**
     * Get user desktop tabs (owns + commons).
     */
    public function getDesktopTabs(User $user = null): array
    {
        // generate the final list of tabs
        $tabs = $this->getCommonDesktopTabs();
        if ($user) {
            $tabs = array_merge(
                $tabs,
                $this->getUserDesktopTabs($user)
            );
        }

        // we rewrite tab position because an admin and a user tab may have the same position
        foreach ($tabs as $index => &$tab) {
            $tab['position'] = $index;
        }

        return $tabs;
    }

    /**
     * Get user desktop own tabs.
     */
    public function getUserDesktopTabs(User $user): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_DESKTOP,
                'user' => $user->getUuid(),
            ],
        ]);

        return $this->formatTabs($tabs['data']);
    }

    /**
     * Get common desktop home tabs.
     */
    public function getCommonDesktopTabs(): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_ADMIN_DESKTOP,
            ],
        ]);

        return $this->formatTabs($tabs['data']);
    }

    /**
     * Get workspace home tabs.
     */
    public function getWorkspaceTabs(Workspace $workspace): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_WORKSPACE,
                'workspace' => $workspace->getUuid(),
            ],
        ]);

        return $this->formatTabs($tabs['data']);
    }

    /**
     * Get administration tabs.
     */
    public function getAdministrationTabs(): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_ADMIN,
            ],
        ]);

        return $this->formatTabs($tabs['data']);
    }

    /**
     * Create a tree from flatten tabs and exclude tabs with no access.
     * It's not done in finder nor serializer because of the complexity of access rules.
     */
    private function formatTabs(array $tabs): array
    {
        $roots = [];
        $children = [];

        foreach ($tabs as $tab) {
            if (empty($tab)) {
                continue; // todo : check why this is required
            }

            if (!$this->authorization->isGranted('OPEN', $tab)) {
                continue;
            }

            if (empty($tab->getParent())) {
                $roots[] = $tab;
            } else {
                if (!isset($children[$tab->getParent()->getUuid()])) {
                    $children[$tab->getParent()->getUuid()] = [];
                }

                $children[$tab->getParent()->getUuid()][] = $tab;
            }
        }

        return array_map(function (HomeTab $root) use ($children) {
            return $this->formatTab($root, $children);
        }, $roots);
    }

    private function formatTab(HomeTab $tab, array $allChildren = []): array
    {
        $serialized = $this->serializer->serialize($tab);
        $children = [];
        if (!empty($allChildren[$tab->getUuid()])) {
            $children = array_map(function (HomeTab $child) use ($allChildren) {
                return $this->formatTab($child, $allChildren);
            }, $allChildren[$tab->getUuid()]);
        }

        // replace children
        $serialized['children'] = $children;

        return $serialized;
    }
}
