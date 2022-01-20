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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/pathtracking")
 */
class PathTrackingController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ToolManager */
    private $toolManager;

    private $userProgressionRepo;
    private $resourceUserEvaluationRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        ObjectManager $om,
        SerializerProvider $serializer,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->toolManager = $toolManager;

        $this->userProgressionRepo = $om->getRepository(UserProgression::class);
        $this->resourceUserEvaluationRepo = $om->getRepository(ResourceUserEvaluation::class);
    }

    /**
     * Fetch all paths tracking of the workspace.
     *
     * @Route("/workspace/{workspace}/paths/tracking", name="claroline_paths_trackings_fetch", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function pathsTrackingFetchAction(Workspace $workspace): JsonResponse
    {
        $tool = $this->toolManager->getToolByName('dashboard');

        if (!$tool || !$this->authorization->isGranted('OPEN', $tool)) {
            throw new AccessDeniedException();
        }

        /** @var Path[] $paths */
        $paths = $this->finder->fetch(
            Path::class,
            ['workspace' => $workspace->getUuid()]
        );
        $data = [];

        foreach ($paths as $path) {
            // Fetches all users who have to do the path
            /** @var ResourceUserEvaluation $resourceEvals */
            $resourceEvals = $this->resourceUserEvaluationRepo->findBy(['resourceNode' => $path->getResourceNode()]);
            $unstartedUsers = [];

            foreach ($resourceEvals as $resourceEval) {
                $user = $resourceEval->getUser();
                $unstartedUsers[$user->getUuid()] = [
                    'id' => $user->getUuid(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'name' => $user->getFirstName().' '.$user->getLastName(),
                ];
            }

            // Reverse steps to proceed the latest steps first as we will only keep the most advanced step the users have done
            /** @var Step[] $steps */
            $steps = array_reverse($path->getOrderedSteps());
            $stepsData = [];
            $usersDone = [];

            foreach ($steps as $step) {
                /** @var UserProgression[] $progressions */
                $progressions = $this->userProgressionRepo->findBy(['step' => $step]);
                $stepUsers = [];

                foreach ($progressions as $progression) {
                    if (in_array($progression->getStatus(), ['seen', 'done'])) {
                        $user = $progression->getUser();
                        $userId = $user->getUuid();

                        if (!isset($usersDone[$userId])) {
                            $stepUsers[] = [
                                'id' => $userId,
                                'username' => $user->getUsername(),
                                'firstName' => $user->getFirstName(),
                                'lastName' => $user->getLastName(),
                                'name' => $user->getFirstName().' '.$user->getLastName(),
                            ];
                            $usersDone[$userId] = true;

                            if (isset($unstartedUsers[$userId])) {
                                unset($unstartedUsers[$userId]);
                            }
                        }
                    }
                }

                $stepsData[] = [
                    'step' => $this->serializer->serialize($step, [Options::SERIALIZE_MINIMAL]),
                    'users' => $stepUsers,
                ];
            }

            $data[] = [
                'path' => $this->serializer->serialize($path->getResourceNode()),
                'steps' => array_reverse($stepsData),
                'unstartedUsers' => array_values($unstartedUsers),
            ];
        }

        return new JsonResponse($data, 200);
    }
}
