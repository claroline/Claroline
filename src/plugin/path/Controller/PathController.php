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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\UserProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

    /** @var UserProgressionManager */
    private $userProgressionManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        UserProgressionManager $userProgressionManager
    ) {
        $this->authorization = $authorization;
        $this->userProgressionManager = $userProgressionManager;
    }

    public function getClass()
    {
        return Path::class;
    }

    public function getName()
    {
        return 'path';
    }

    /**
     * Update step progression for an user.
     *
     * @Route("/step/{id}/progression/update", name="innova_path_progression_update", methods={"PUT"})
     * @EXT\ParamConverter("step", class="InnovaPathBundle:Step", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateProgressionAction(Step $step, User $user, Request $request): JsonResponse
    {
        $node = $step->getPath()->getResourceNode();

        $this->checkPermission('OPEN', $node, [], true);

        $status = $this->decodeRequest($request)['status'];
        $this->userProgressionManager->update($step, $user, $status);
        $resourceUserEvaluation = $this->userProgressionManager->getResourceUserEvaluation($step->getPath(), $user);

        return new JsonResponse([
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation),
            'userProgression' => [
                'stepId' => $step->getUuid(),
                'status' => $status,
            ],
        ]);
    }

    /**
     * Fetch user progressions for path.
     *
     * @Route("/{id}/progressions/fetch", name="innova_path_progressions_fetch", methods={"GET"})
     * @EXT\ParamConverter("path", class="InnovaPathBundle:Path\Path", options={"mapping": {"id": "uuid"}})
     */
    public function progressionsFetchAction(Path $path, Request $request): JsonResponse
    {
        $node = $path->getResourceNode();

        $this->checkPermission('EDIT', $path->getResourceNode(), [], true);

        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['resourceNode'] = [$node->getUuid()];

        return new JsonResponse(
            $this->finder->search(ResourceUserEvaluation::class, $params, [Options::SERIALIZE_MINIMAL])
        );
    }

    /**
     * @Route("/{id}/attempt", name="innova_path_current_attempt", methods={"GET"})
     * @EXT\ParamConverter("path", class="InnovaPathBundle:Path\Path", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function getAttemptAction(Path $path, User $user = null)
    {
        $this->checkPermission('OPEN', $path->getResourceNode(), [], true);

        $attempt = null;
        if ($user) {
            $attempt = $this->serializer->serialize($this->userProgressionManager->getCurrentAttempt($path, $user));
        }

        return new JsonResponse($attempt);
    }

    /**
     * Fetch user progressions for path.
     *
     * @Route("/{id}/user/{user}/steps/progression/fetch", name="innova_path_user_steps_progression_fetch", methods={"GET"})
     * @EXT\ParamConverter("path", class="InnovaPathBundle:Path\Path", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", class="ClarolineCoreBundle:User", options={"mapping": {"user": "uuid"}})
     */
    public function userStepsProgressionFetchAction(Path $path, User $user): JsonResponse
    {
        $this->checkPermission('EDIT', $path->getResourceNode(), [], true);

        return new JsonResponse(
            $this->userProgressionManager->getStepsProgressionForUser($path, $user)
        );
    }

    /**
     * This should be managed by Core when possible.
     *
     * @Route("/{id}/progression/csv", name="innova_path_users_progression_csv", methods={"GET"})
     * @EXT\ParamConverter("path", class="InnovaPathBundle:Path\Path", options={"mapping": {"id": "uuid"}})
     */
    public function exportProgressionCsvAction(Path $path): StreamedResponse
    {
        $this->checkPermission('EDIT', $path->getResourceNode(), [], true);

        $fileName = TextNormalizer::toKey("progression-{$path->getResourceNode()->getSlug()}");

        $evaluations = $this->finder->searchEntities(ResourceUserEvaluation::class, [
            'filters' => ['resourceNode' => $path->getResourceNode()->getUuid()],
        ]);

        return new StreamedResponse(function () use ($evaluations) {
            // Prepare CSV file
            $handle = fopen('php://output', 'w+');

            // Create header
            fputcsv($handle, [
                'first_name',
                'last_name',
                'progression',
                'progression_max',
            ], ';', '"');

            foreach ($evaluations['data'] as $evaluation) {
                fputcsv($handle, [
                    $evaluation->getUser()->getFirstName(),
                    $evaluation->getUser()->getLastName(),
                    $evaluation->getProgression(),
                    $evaluation->getProgressionMax(),
                ], ';', '"');
            }

            fclose($handle);

            return $handle;
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]);
    }
}
