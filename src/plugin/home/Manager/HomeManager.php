<?php

namespace Claroline\HomeBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HomeManager
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly RequestStack $requestStack,
        private readonly SerializerProvider $serializer
    ) {
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
    public function unlock(HomeTab $tab, Request $request): void
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
    public function formatTabs(array $tabs, array $options = []): array
    {
        $roots = [];
        $children = [];

        foreach ($tabs as $tab) {
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

            // check if the current user already has unlocked the tab
            // maybe store it another way to avoid require it each time the user session expires
            return !empty($currentRequest->getSession()->get($tab->getUuid()));
        }

        // the current tab not require a code
        return true;
    }
}
