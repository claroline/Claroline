<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Innova\PathBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\EvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/path")
 */
class PathController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly EvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
    }

    public function getClass(): string
    {
        return Path::class;
    }

    public function getName(): string
    {
        return 'path';
    }

    public function getIgnore(): array
    {
        // we only keep update method
        return ['list', 'get', 'create', 'deleteBulk'];
    }

    /**
     * Update step progression for a user.
     *
     * @Route("/step/{id}/progression", name="innova_path_progression_update", methods={"PUT"})
     *
     * @EXT\ParamConverter("step", class="Innova\PathBundle\Entity\Step", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateProgressionAction(Step $step, User $user, Request $request): JsonResponse
    {
        $node = $step->getPath()->getResourceNode();

        $this->checkPermission('OPEN', $node, [], true);

        $stepProgression = $this->evaluationManager->update($step, $user, $this->decodeRequest($request)['status']);

        return new JsonResponse(array_merge($this->getUserProgression($step->getPath(), $user), [
            'userProgression' => [
                'stepId' => $step->getUuid(),
                'status' => $stepProgression->getStatus(),
            ],
        ]));
    }

    /**
     * Gets current user progression in the Path.
     * It includes, the Path evaluation, current attempt and evaluations for the embedded resources.
     *
     * @Route("/{id}/attempt", name="innova_path_user_progression", methods={"GET"})
     *
     * @EXT\ParamConverter("path", class="Innova\PathBundle\Entity\Path\Path", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function getUserProgressionAction(Path $path, User $user = null): JsonResponse
    {
        $this->checkPermission('OPEN', $path->getResourceNode(), [], true);

        if ($user) {
            return new JsonResponse($this->getUserProgression($path, $user));
        }

        return new JsonResponse([
            'attempt' => null,
            'userEvaluation' => null,
            'resourceEvaluations' => [],
        ]);
    }

    /**
     * Fetch user progressions for path.
     *
     * @Route("/{id}/user/{user}/steps/progression/fetch", name="innova_path_user_steps_progression_fetch", methods={"GET"})
     *
     * @EXT\ParamConverter("path", class="Innova\PathBundle\Entity\Path\Path", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     */
    public function userStepsProgressionFetchAction(Path $path, User $user): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $path->getResourceNode(), [], true);

        return new JsonResponse([
            'lastAttempt' => $this->serializer->serialize(
                $this->evaluationManager->getCurrentAttempt($path, $user, false)
            ),
            'progression' => $this->evaluationManager->getStepsProgressionForUser($path, $user),
            'resourceEvaluations' => array_map(function (ResourceUserEvaluation $resourceEvaluation) {
                return $this->serializer->serialize($resourceEvaluation);
            }, $this->evaluationManager->getRequiredEvaluations($path, $user)),
        ]);
    }

    private function getUserProgression(Path $path, User $user): array
    {
        $attempt = $this->evaluationManager->getCurrentAttempt($path, $user);
        // get embedded resources evaluations
        $resourceEvaluations = $this->evaluationManager->getRequiredEvaluations($path, $user);

        return [
            'attempt' => $this->serializer->serialize($attempt),
            'userEvaluation' => $this->serializer->serialize($attempt->getResourceUserEvaluation(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'resourceEvaluations' => array_map(function (ResourceUserEvaluation $resourceEvaluation) {
                return $this->serializer->serialize($resourceEvaluation);
            }, $resourceEvaluations),
        ];
    }
}
