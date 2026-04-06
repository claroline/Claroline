<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Component\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\ProjectBundle\Entity\Correction;
use Claroline\ProjectBundle\Entity\EvaluationCriteria;
use Claroline\ProjectBundle\Entity\Milestone;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Entity\Submission;
use Claroline\ProjectBundle\Serializer\CorrectionSerializer;
use Claroline\ProjectBundle\Serializer\ProjectSerializer;
use Claroline\ProjectBundle\Serializer\SubmissionSerializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ProjectResource extends ResourceComponent implements EvaluatedResourceInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly ProjectSerializer $projectSerializer,
        private readonly SubmissionSerializer $submissionSerializer,
        private readonly CorrectionSerializer $correctionSerializer
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_project';
    }

    public static function supportsScore(): bool
    {
        return true;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    /** @param Project $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        $data = [
            'project' => $this->projectSerializer->serialize($resource),
        ];

        if ($user) {
            // Get the current user's submissions
            $submissions = $this->om->getRepository(Submission::class)->findBy([
                'project' => $resource,
                'user' => $user,
            ]);

            $data['mySubmissions'] = array_map(function (Submission $submission) {
                return $this->submissionSerializer->serialize($submission);
            }, $submissions);

            // Get visible corrections for the current user (only submitted ones)
            $corrections = $this->om->getRepository(Correction::class)->findBy([
                'project' => $resource,
                'user' => $user,
                'status' => Correction::STATUS_SUBMITTED,
            ]);

            $data['myCorrections'] = array_map(function (Correction $correction) {
                return $this->correctionSerializer->serialize($correction);
            }, $corrections);
        }

        return $data;
    }

    /**
     * @param Project $original
     * @param Project $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        // Copy milestones
        foreach ($original->getMilestones() as $milestone) {
            $newMilestone = new Milestone();
            $newMilestone->setTitle($milestone->getTitle());
            $newMilestone->setInstructions($milestone->getInstructions());
            $newMilestone->setPosition($milestone->getPosition());
            $newMilestone->setDeadlineType($milestone->getDeadlineType());
            $newMilestone->setDeadlineDate($milestone->getDeadlineDate());
            $newMilestone->setDeadlineDays($milestone->getDeadlineDays());
            $copy->addMilestone($newMilestone);
        }

        // Copy criteria
        foreach ($original->getCriteria() as $criterion) {
            $newCriterion = new EvaluationCriteria();
            $newCriterion->setLabel($criterion->getLabel());
            $newCriterion->setDescription($criterion->getDescription());
            $newCriterion->setScoreMax($criterion->getScoreMax());
            $newCriterion->setPosition($criterion->getPosition());
            $copy->addCriterion($newCriterion);
        }
    }

    /** @param Project $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        return true;
    }
}
