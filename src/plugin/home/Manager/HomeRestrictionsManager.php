<?php

namespace Claroline\HomeBundle\Manager;

use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HomeRestrictionsManager
{
    private const NO_RIGHTS = 'NO_RIGHTS';
    private const INVALID_DATES = 'INVALID_DATES';
    private const ACCESS_CODE = 'ACCESS_CODE';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function getError(HomeTab $homeTab, array $userRoles): ?array
    {
        if (!$this->hasRights($homeTab, $userRoles)) {
            return [
                'code' => self::NO_RIGHTS,
                'message' => 'You don\'t have the permissions to access this home tab.',
            ];
        }

        if (!$this->isStarted($homeTab) || $this->isEnded($homeTab)) {
            return [
                'code' => self::INVALID_DATES,
                'message' => !$this->isStarted($homeTab) ?
                    'The access period of the home tab is not started yet.' :
                    'The access period of the home tab is ended.',
                'additional' => [
                    'startDate' => DateNormalizer::normalize($homeTab->getAccessibleFrom()),
                    'endDate' => DateNormalizer::normalize($homeTab->getAccessibleUntil()),
                ],
            ];
        }

        if (!$this->isUnlocked($homeTab)) {
            return [
                'code' => self::ACCESS_CODE,
                'message' => 'This home tab requires an access code to be opened.',
            ];
        }

        return null;
    }

    private function hasRights(HomeTab $homeTab, array $userRoles): bool
    {
        $roles = $homeTab->getRoles()->toArray();
        if (empty($roles)) {
            return true;
        }

        foreach ($roles as $role) {
            if (in_array($role->getName(), $userRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Submits a code to unlock a home tab.
     * NB. The tab will stay unlocked as long as the user session stays alive.
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
