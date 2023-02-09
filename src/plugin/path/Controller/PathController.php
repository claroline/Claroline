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

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var EvaluationManager */
    private $evaluationManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
        $this->evaluationManager = $evaluationManager;
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
        return ['list', 'get', 'create', 'deleteBulk', 'copyBulk', 'exist', 'find'];
    }

    /**
     * Update step progression for an user.
     *
     * @Route("/step/{id}/progression", name="innova_path_progression_update", methods={"PUT"})
     * @EXT\ParamConverter("step", class="Innova\PathBundle\Entity\Step", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateProgressionAction(Step $step, User $user, Request $request): JsonResponse
    {
        $node = $step->getPath()->getResourceNode();

        $this->checkPermission('OPEN', $node, [], true);

        $progression = $this->evaluationManager->update($step, $user, $this->decodeRequest($request)['status']);

        // get updated version of the current path evaluation
        $resourceUserEvaluation = $this->evaluationManager->getResourceUserEvaluation($step->getPath(), $user);

        // get updated version of the embedded resources evaluations
        $resourceEvaluations = $this->evaluationManager->getRequiredEvaluations($step->getPath(), $user);

        return new JsonResponse([
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
            'resourceEvaluations' => array_map(function (ResourceUserEvaluation $resourceEvaluation) {
                return $this->serializer->serialize($resourceEvaluation);
            }, $resourceEvaluations),
            'userProgression' => [
                'stepId' => $step->getUuid(),
                'status' => $progression->getStatus(),
            ],
        ]);
    }

    /**
     * @Route("/{id}/attempt", name="innova_path_current_attempt", methods={"GET"})
     * @EXT\ParamConverter("path", class="Innova\PathBundle\Entity\Path\Path", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function getAttemptAction(Path $path, ?User $user = null): JsonResponse
    {
        $this->checkPermission('OPEN', $path->getResourceNode(), [], true);

        $attempt = null;
        $resourceEvaluations = [];
        if ($user) {
            $attempt = $this->serializer->serialize($this->evaluationManager->getCurrentAttempt($path, $user));
            $resourceEvaluations = array_map(function (ResourceUserEvaluation $resourceEvaluation) {
                return $this->serializer->serialize($resourceEvaluation);
            }, $this->evaluationManager->getRequiredEvaluations($path, $user));
        }

        return new JsonResponse([
            'attempt' => $attempt,
            'resourceEvaluations' => $resourceEvaluations,
        ]);
    }

    /**
     * Fetch user progressions for path.
     *
     * @Route("/{id}/user/{user}/steps/progression/fetch", name="innova_path_user_steps_progression_fetch", methods={"GET"})
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
}
