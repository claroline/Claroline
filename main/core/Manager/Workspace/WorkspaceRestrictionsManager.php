<?php

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * WorkspaceRestrictionsManager.
 *
 * It validates access restrictions on Workspaces.
 *
 * @todo merge restrictions checks with ResourceRestrictionsManager.
 */
class WorkspaceRestrictionsManager
{
    /** @var SessionInterface */
    private $session;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /**
     * ResourceRestrictionsManager constructor.
     *
     * @param SessionInterface              $session
     * @param AuthorizationCheckerInterface $authorization
     * @param WorkspaceManager              $workspaceManager
     */
    public function __construct(
        SessionInterface $session,
        AuthorizationCheckerInterface $authorization,
        WorkspaceManager $workspaceManager
    ) {
        $this->session = $session;
        $this->authorization = $authorization;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * Checks access restrictions of a workspace.
     *
     * @param Workspace $workspace
     *
     * @return bool
     */
    public function isGranted(Workspace $workspace): bool
    {
        return $this->hasRights($workspace)
            && ($this->isStarted($workspace) && !$this->isEnded($workspace))
            && $this->isUnlocked($workspace)
            && $this->isIpAuthorized($workspace);
    }

    /**
     * Gets the list of access error for a workspace and a user.
     *
     * @param Workspace $workspace
     * @param User      $user
     *
     * @return array
     */
    public function getErrors(Workspace $workspace, User $user = null): array
    {
        if (!$this->isGranted($workspace)) {
            // return restrictions details
            $errors = [
                'noRights' => !$this->hasRights($workspace),
                'selfRegistration' => $workspace->getSelfRegistration(),
            ];

            if ($user) {
                $errors['pendingRegistration'] = $this->workspaceManager->isUserInValidationQueue($workspace, $user);
            }

            // optional restrictions
            // we return them only if they are enabled
            if (!empty($workspace->getAccessCode())) {
                $errors['locked'] = !$this->isUnlocked($workspace);
            }

            if (!empty($workspace->getAccessibleFrom()) || !empty($workspace->getAccessibleUntil())) {
                $errors['notStarted'] = !$this->isStarted($workspace);
                $errors['startDate'] = $workspace->getAccessibleFrom() ?
                    $workspace->getAccessibleFrom()->format('d/m/Y') :
                    null;
                $errors['ended'] = $this->isEnded($workspace);
                $errors['endDate'] = $workspace->getAccessibleUntil() ?
                    $workspace->getAccessibleUntil()->format('d/m/Y') :
                    null;
            }

            if (!empty($workspace->getAllowedIps())) {
                $errors['invalidLocation'] = !$this->isIpAuthorized($workspace);
            }

            return $errors;
        }

        return [];
    }

    /**
     * Checks if a user has at least the right to access the workspace.
     *
     * @param Workspace $workspace
     *
     * @return bool
     */
    public function hasRights(Workspace $workspace): bool
    {
        return $this->authorization->isGranted('open', $workspace);
    }

    /**
     * Checks if the access period of the workspace is started.
     *
     * @param Workspace $workspace
     *
     * @return bool
     */
    public function isStarted(Workspace $workspace): bool
    {
        return empty($workspace->getAccessibleFrom()) || $workspace->getAccessibleFrom() <= new \DateTime();
    }

    /**
     * Checks if the access period of the workspace is over.
     *
     * @param Workspace $workspace
     *
     * @return bool
     */
    public function isEnded(Workspace $workspace): bool
    {
        return !empty($workspace->getAccessibleUntil()) && $workspace->getAccessibleUntil() <= new \DateTime();
    }

    /**
     * Checks if the ip of the current user is allowed to access the workspace.
     *
     * @param Workspace $workspace
     *
     * @return bool
     *
     * @todo works just with IPv4, should be working with IPv6
     */
    public function isIpAuthorized(Workspace $workspace): bool
    {
        $allowed = $workspace->getAllowedIps();
        if (!empty($allowed)) {
            $currentParts = explode('.', $_SERVER['REMOTE_ADDR']);

            foreach ($allowed as $allowedIp) {
                $allowedParts = explode('.', $allowedIp);
                $allowBlock = [];

                foreach ($allowedParts as $key => $val) {
                    if (isset($currentParts[$key])) {
                        $allowBlock[] = ($val === $currentParts[$key] || '*' === $val);
                    }
                }

                if (!in_array(false, $allowBlock)) {
                    return true;
                }
            }

            // the current user ip is not in the allowed list
            return false;
        }

        // the current workspace does not restrict ips
        return true;
    }

    /**
     * Checks if a workspace is unlocked.
     * (aka it has no access code, or user has already submitted it).
     *
     * @param Workspace $workspace
     *
     * @return bool
     */
    public function isUnlocked(Workspace $workspace): bool
    {
        if ($workspace->getAccessCode()) {
            // check if the current user already has unlocked the workspace
            // maybe store it another way to avoid require it each time the user session expires
            return !empty($this->session->get($workspace->getUuid()));
        }

        // the current workspace does not require a code
        return true;
    }

    /**
     * Submits a code to unlock a workspace.
     * NB. The workspace will stay unlocked as long as the user session stay alive.
     *
     * @param Workspace $workspace - The workspace to unlock
     * @param string    $code      - The code sent by the user
     *
     * @throws InvalidDataException - If the submitted code is incorrect
     */
    public function unlock(Workspace $workspace, $code = null)
    {
        //if a code is defined
        if ($accessCode = $workspace->getAccessCode()) {
            if (empty($code) || $accessCode !== $code) {
                $this->session->set($workspace->getUuid(), false);

                throw new InvalidDataException('Invalid code sent');
            }

            $this->session->set($workspace->getUuid(), true);
        }
    }
}
