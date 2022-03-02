<?php

namespace Claroline\HomeBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HomeManager
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var RequestStack */
    private $requestStack;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        RequestStack $requestStack,
        FinderProvider $finder,
        SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
        $this->requestStack = $requestStack;
        $this->finder = $finder;
        $this->serializer = $serializer;
    }

    /**
     * Get platform home tabs.
     */
    public function getHomeTabs(array $options = []): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_HOME,
            ],
        ]);

        return $this->formatTabs($tabs['data'], $options);
    }

    /**
     * Get user desktop tabs (owns + commons).
     */
    public function getDesktopTabs(User $user = null, array $options = []): array
    {
        // generate the final list of tabs
        $tabs = $this->getCommonDesktopTabs($options);
        if ($user) {
            $tabs = array_merge(
                $tabs,
                $this->getUserDesktopTabs($user, $options)
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
    public function getUserDesktopTabs(User $user, array $options = []): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_DESKTOP,
                'user' => $user->getUuid(),
            ],
        ]);

        return $this->formatTabs($tabs['data'], $options);
    }

    /**
     * Get common desktop home tabs.
     */
    public function getCommonDesktopTabs(array $options = []): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_ADMIN_DESKTOP,
            ],
        ]);

        return $this->formatTabs($tabs['data'], $options);
    }

    /**
     * Get workspace home tabs.
     */
    public function getWorkspaceTabs(Workspace $workspace, array $options = []): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_WORKSPACE,
                'workspace' => $workspace->getUuid(),
            ],
        ]);

        return $this->formatTabs($tabs['data'], $options);
    }

    /**
     * Get administration tabs.
     */
    public function getAdministrationTabs(array $options = []): array
    {
        $tabs = $this->finder->searchEntities(HomeTab::class, [
            'filters' => [
                'context' => HomeTab::TYPE_ADMIN,
            ],
        ]);

        return $this->formatTabs($tabs['data'], $options);
    }

    public function getRestrictionsErrors(HomeTab $homeTab): array
    {
        $errors = [];

        if (!$this->isStarted($homeTab) || $this->isEnded($homeTab) || !$this->isUnlocked($homeTab)) {
            if (!empty($homeTab->getAccessCode()) && !$this->isUnlocked($homeTab)) {
                $errors['locked'] = !$this->isUnlocked($homeTab);
            }

            if (!empty($homeTab->getAccessibleFrom()) || !empty($homeTab->getAccessibleUntil())) {
                $errors['notStarted'] = !$this->isStarted($homeTab);
                $errors['startDate'] = DateNormalizer::normalize($homeTab->getAccessibleFrom());
                $errors['ended'] = $this->isEnded($homeTab);
                $errors['endDate'] = DateNormalizer::normalize($homeTab->getAccessibleUntil());
            }
        }

        return $errors;
    }

    /**
     * Submits a code to unlock a home tab.
     * NB. The tab will stay unlocked as long as the user session stay alive.
     */
    public function unlock(HomeTab $tab, Request $request)
    {
        $accessCode = $tab->getAccessCode();
        if ($accessCode) {
            $code = json_decode($request->getContent(), true)['code'];
            if (empty($code) || $accessCode !== $code) {
                $request->getSession()->set($tab->getUuid(), false);

                throw new InvalidDataException('Invalid code sent');
            }

            $request->getSession()->set($tab->getUuid(), true);
        }
    }

    /**
     * Create a tree from flatten tabs and exclude tabs with no access.
     * It's not done in finder nor serializer because of the complexity of access rules.
     */
    private function formatTabs(array $tabs, array $options = []): array
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

    /**
     * Checks if the access period of the tab is started.
     */
    private function isStarted(HomeTab $tab): bool
    {
        return empty($tab->getAccessibleFrom()) || $tab->getAccessibleFrom() <= new \DateTime();
    }

    /**
     * Checks if the access period of the tab is over.
     */
    private function isEnded(HomeTab $tab): bool
    {
        return !empty($tab->getAccessibleUntil()) && $tab->getAccessibleUntil() <= new \DateTime();
    }

    /**
     * Checks if a resource is unlocked.
     * (aka it has no access code, or user has already submitted it).
     */
    private function isUnlocked(HomeTab $tab): bool
    {
        if ($tab->getAccessCode()) {
            $currentRequest = $this->requestStack->getCurrentRequest();

            // check if the current user already has unlocked the resource
            // maybe store it another way to avoid require it each time the user session expires
            return !empty($currentRequest->getSession()->get($tab->getUuid()));
        }

        // the current resource does not require a code
        return true;
    }
}
