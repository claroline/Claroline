<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller\UserEvaluation;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\Certificate\SequenceCertificate;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\Sequence\Step;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Manager\ExportManager;
use Claroline\EvaluationBundle\Manager\SequenceEvaluationManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Manages user evaluations for sequences {@see SequenceEvaluation}}.
 */
#[Route(path: '/sequence_evaluation')]
class SequenceEvaluationController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ToolManager $toolManager,
        private readonly SequenceEvaluationManager $evaluationManager,
        private readonly ExportManager $exportManager,
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns the list of user evaluations for a Sequence.
     */
    #[Route(path: '/{parentType}/{parentId}', name: 'apiv2_sequence_evaluation_list', requirements: ['parentType' => '(workspace|sequence)?'], methods: ['GET'])]
    public function listAction(
        ?string $parentType = null,
        ?string $parentId = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        switch ($parentType) {
            case 'sequence':
                $sequence = $this->om->getRepository(Sequence::class)->findOneBy(['uuid' => $parentId]);
                $manager = $this->checkPermission('FOLLOW', $sequence);

                $finderQuery->addFilter('sequence', $sequence->getUuid());
                break;

            case 'workspace':
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $parentId]);
                $progressionTool = $this->toolManager->getOrderedTool('progression', WorkspaceContext::getName(), $parentId);
                $manager = $this->checkPermission('FOLLOW', $progressionTool);

                $finderQuery->addFilter('sequence.workspace', $workspace->getUuid());
                break;

            default:
                $progressionTool = $this->toolManager->getOrderedTool('progression', DesktopContext::getName());
                $manager = $this->checkPermission('FOLLOW', $progressionTool);
                break;
        }

        if (!$manager) {
            // only display evaluation of the current user
            /** @var User $user */
            $user = $this->tokenStorage->getToken()?->getUser();
            $finderQuery->addFilter('user', $user->getUuid());
        }

        $evaluations = $this->crud->search(SequenceEvaluation::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $evaluations->toResponse();
    }

    #[Route(path: '/{sequenceId}/csv', name: 'apiv2_sequence_evaluation_csv', methods: ['GET'])]
    public function exportCsvAction(
        #[MapEntity(mapping: ['sequenceId' => 'uuid'])]
        Sequence $sequence
    ): StreamedResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        return new StreamedResponse(function () use ($sequence): void {
            $this->exportManager->exportSequenceEvaluations($sequence);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toFilename($sequence->getName()).'.csv',
        ]);
    }

    /**
     * Update step progression for a user.
     */
    #[Route(path: '/{id}', name: 'apiv2_sequence_evaluation_update', methods: ['PUT'])]
    public function updateProgressionAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Step $step,
        #[CurrentUser]
        ?User $user
    ): JsonResponse {
        if (null === $user) {
            return new JsonResponse(null, 204);
        }

        $sequence = $step->getSequence();

        $this->checkPermission('OPEN', $sequence, [], true);

        $userEvaluation = $this->evaluationManager->getUserEvaluation($sequence, $user, false);
        $this->evaluationManager->updateUserEvaluation($userEvaluation, $step);

        return new JsonResponse([
            'evaluation' => $this->serializer->serialize($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
            'progression' => $this->evaluationManager->getUserProgression($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
        ]);
    }

    #[Route(path: '/{evaluationId}', name: 'apiv2_sequence_evaluation_get', methods: ['GET'])]
    public function geAction(
        #[MapEntity(mapping: ['evaluationId' => 'uuid'])]
        SequenceEvaluation $sequenceEvaluation
    ): JsonResponse {
        $this->checkPermission('OPEN', $sequenceEvaluation, [], true);

        $sequence = $sequenceEvaluation->getSequence();

        $certificates = [];
        if ($sequenceEvaluation->isCertified()) {
            $certificates = $this->om->getRepository(SequenceCertificate::class)->findBy([
                'evaluation' => $sequenceEvaluation,
            ], [
                'obtentionDate' => 'DESC',
                'issueDate' => 'DESC',
            ]);
        }

        $archives = [];
        if (!$sequenceEvaluation->isArchived()) {
            $archives = $this->om->getRepository(SequenceEvaluation::class)->findBy([
                'user' => $sequenceEvaluation->getUser(),
                'sequence' => $sequenceEvaluation->getSequence(),
                'archived' => true,
            ]);
        }

        return new JsonResponse([
            'parameters' => [
                'successCondition' => $sequence->getSuccessCondition(),
            ],
            'evaluation' => $this->serializer->serialize($sequenceEvaluation),
            'progression' => $this->evaluationManager->getUserProgression($sequenceEvaluation),
            'certificates' => array_map(function (SequenceCertificate $certificate) {
                return $this->serializer->serialize($certificate);
            }, $certificates),
            'archives' => array_map(function (SequenceEvaluation $archivedEvaluation) {
                return $this->serializer->serialize($archivedEvaluation);
            }, $archives),
        ]);
    }

    #[Route(path: '/', name: 'apiv2_sequence_evaluation_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request): JsonResponse
    {
        // no need to secure endpoint Crud will do it for us

        $evaluationIds = $this->decodeRequest($request);
        $evaluations = $this->om->getRepository(SequenceEvaluation::class)->findBy([
            'uuid' => $evaluationIds,
        ]);

        foreach ($evaluations as $evaluation) {
            $this->crud->delete($evaluation);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Initializes evaluations for all the users of a workspace.
     */
    #[Route(path: '/{sequenceId}/init', name: 'apiv2_sequence_evaluation_init', methods: ['PUT'])]
    public function initializeAction(
        #[MapEntity(mapping: ['sequenceId' => 'uuid'])]
        Sequence $sequence
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $sequence, [], true);

        $this->evaluationManager->initializeEvaluations($sequence);

        return new JsonResponse(null, 204);
    }

    /**
     * Recalculates (score, status, progression, ...) evaluations for all the users of a sequence.
     */
    #[Route(path: '/{sequenceId}/recompute', name: 'apiv2_sequence_evaluation_recompute', methods: ['PUT'])]
    public function recomputeAction(
        #[MapEntity(mapping: ['sequenceId' => 'uuid'])]
        Sequence $sequence,
        Request $request
    ): JsonResponse {
        $evaluationIds = $this->decodeRequest($request);

        // recompute all the sequence evaluations
        if (empty($evaluationIds)) {
            $this->checkPermission('FOLLOW', $sequence, [], true);
            $this->evaluationManager->recomputeEvaluations($sequence);

            return new JsonResponse(null, 204);
        }

        // recompute selected evaluations
        $evaluations = $this->om->getRepository(SequenceEvaluation::class)->findBy(['uuid' => $evaluationIds]);
        foreach ($evaluations as $evaluation) {
            if ($this->checkPermission('ADMINISTRATE', $evaluation)) {
                $this->evaluationManager->refreshEvaluation($evaluation);
            }
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Removes all user evaluations for the sequence.
     */
    #[Route(path: '/{sequenceId}', name: 'apiv2_sequence_evaluation_purge', methods: ['DELETE'])]
    public function purgeAction(
        #[MapEntity(mapping: ['sequenceId' => 'uuid'])]
        Sequence $sequence
    ): JsonResponse {
        $this->checkPermission('ADMINISTRATE', $sequence, [], true);

        $this->evaluationManager->purgeEvaluations($sequence);

        return new JsonResponse(null, 204);
    }
}
