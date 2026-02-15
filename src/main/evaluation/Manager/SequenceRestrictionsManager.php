<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * SequenceRestrictionsManager.
 *
 * It validates access restrictions on Sequence.
 */
class SequenceRestrictionsManager
{
    private const NO_RIGHTS = 'NO_RIGHTS';
    private const NOT_PUBLISHED = 'NOT_PUBLISHED';
    private const INVALID_DATES = 'INVALID_DATES';
    private const ACCESS_CODE = 'ACCESS_CODE';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om
    ) {
    }

    /**
     * Checks access restrictions of a resource.
     *
     * @param string[] $userRoles
     */
    public function isGranted(Sequence $sequence, array $userRoles): bool
    {
        return $this->hasRights($sequence, $userRoles)
            && !$sequence->isArchived()
            && $sequence->isPublished()
            && ($this->isStarted($sequence) && !$this->isEnded($sequence))
            && $this->isUnlocked($sequence);
    }

    public function getError(Sequence $sequence, array $userRoles): ?array
    {
        if (!$this->hasRights($sequence, $userRoles)) {
            return [
                'code' => self::NO_RIGHTS,
                'message' => 'You don\'t have the permissions to access this sequence.',
            ];
        }

        if ($sequence->isArchived() || !$sequence->isPublished()) {
            return [
                'code' => self::NOT_PUBLISHED,
                'message' => !$sequence->isPublished() ?
                    'The sequence is not published.' :
                    'The sequence is archived.',
                'additional' => [
                    'archived' => $sequence->isArchived(),
                ],
            ];
        }

        if (!$this->isStarted($sequence) || $this->isEnded($sequence)) {
            return [
                'code' => self::INVALID_DATES,
                'message' => !$this->isStarted($sequence) ?
                    'The access period of the sequence is not started yet.' :
                    'The access period of the sequence is ended.',
                'additional' => [
                    'startDate' => DateNormalizer::normalize($sequence->getAccessibleFrom()),
                    'endDate' => DateNormalizer::normalize($sequence->getAccessibleUntil()),
                ],
            ];
        }

        if (!$this->isUnlocked($sequence)) {
            return [
                'code' => self::ACCESS_CODE,
                'message' => 'This sequence requires an access code to be opened.',
            ];
        }

        return null;
    }

    /**
     * Checks if a user has the open permission on the sequence.
     *
     * @param string[] $userRoles
     */
    public function hasRights(Sequence $sequence, array $userRoles): bool
    {
        if ($sequence->isPublic()) {
            return true;
        }

        $isAdmin = false;

        $workspace = $sequence->getWorkspace();
        if ($workspace) {
            $isAdmin = $this->authorization->isGranted('administrate', $workspace);
        }

        return $isAdmin || $this->om->getRepository(Sequence::class)->hasAssignedRoles($sequence, $userRoles);
    }

    /**
     * Checks if the access period of the sequence is started.
     */
    public function isStarted(Sequence $sequence): bool
    {
        return empty($sequence->getAccessibleFrom()) || $sequence->getAccessibleFrom() <= new \DateTime();
    }

    /**
     * Checks if the access period of the sequence is over.
     */
    public function isEnded(Sequence $sequence): bool
    {
        return !empty($sequence->getAccessibleUntil()) && $sequence->getAccessibleUntil() <= new \DateTime();
    }

    /**
     * Checks if a sequence is unlocked.
     * (aka it has no access code, or user has already submitted it).
     */
    public function isUnlocked(Sequence $sequence): bool
    {
        if ($sequence->getAccessCode()) {
            $currentRequest = $this->requestStack->getCurrentRequest();

            // check if the current user already has unlocked the sequence
            // maybe store it another way to avoid require it each time the user session expires
            return !empty($currentRequest->getSession()->get($sequence->getUuid()));
        }

        // the current sequence does not require a code
        return true;
    }

    /**
     * Submits a code to unlock a sequence.
     * NB. The sequence will stay unlocked as long as the user session stays alive.
     */
    public function unlock(Sequence $sequence, ?string $code = null): void
    {
        $accessCode = $sequence->getAccessCode();
        if ($accessCode) {
            $currentRequest = $this->requestStack->getCurrentRequest();

            if (empty($code) || $accessCode !== $code) {
                $currentRequest->getSession()->set($sequence->getUuid(), false);

                throw new InvalidDataException('Invalid code sent');
            }

            $currentRequest->getSession()->set($sequence->getUuid(), true);
        }
    }
}
