<?php

namespace Claroline\HomeBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Role;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HomeManager
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer
    ) {
    }

    /**
     * Create a tree from flattened tabs and exclude tabs with no access.
     * It's not done in finder nor serializer because of the complexity of access rules.
     *
     * @param HomeTab[] $tabs
     */
    public function formatTabs(array $tabs, array $options = []): array
    {
        $userRoles = $this->tokenStorage->getToken()?->getRoleNames() ?? [];
        $roots = [];
        $children = [];

        foreach ($tabs as $tab) {
            if (0 !== $tab->getRoles()->count() && !$this->authorization->isGranted('EDIT', $tab)) {
                $tabRoles = array_map(function (Role $role) {
                    return $role->getName();
                }, $tab->getRoles()->toArray());

                if (empty(array_intersect($tabRoles, $userRoles))) {
                    continue;
                }
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

        return array_map(function (HomeTab $root) use ($children, $options) {
            return $this->formatTab($root, $children, $options);
        }, $roots);
    }

    private function formatTab(HomeTab $tab, array $allChildren = [], array $options = []): array
    {
        $serialized = $this->serializer->serialize($tab, $options);
        $children = [];
        if (!empty($allChildren[$tab->getUuid()])) {
            $children = array_map(function (HomeTab $child) use ($allChildren, $options) {
                return $this->formatTab($child, $allChildren, $options);
            }, $allChildren[$tab->getUuid()]);
        }

        // replace children
        $serialized['children'] = $children;

        return $serialized;
    }
}
