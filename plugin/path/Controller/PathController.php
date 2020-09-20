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
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\UserProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/path")
 */
class PathController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var UserProgressionManager */
    private $userProgressionManager;

    /**
     * PathController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param UserProgressionManager        $userProgressionManager
     */
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

    /**
     * Update step progression for an user.
     *
     * @Route("/step/{id}/progression/update", name="innova_path_progression_update", methods={"PUT"})
     * @EXT\ParamConverter(
     *     "step",
     *     class="InnovaPathBundle:Step",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Step    $step
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateProgressionAction(Step $step, User $user, Request $request)
    {
        $node = $step->getPath()->getResourceNode();

        if (!$this->authorization->isGranted('OPEN', new ResourceCollection([$node]))) {
            throw new AccessDeniedException('Operation "OPEN" cannot be done on object'.get_class($node));
        }
        $status = $this->decodeRequest($request)['status'];
        $this->userProgressionManager->update($step, $user, $status, true);
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
     * @Route(
     *     "/{id}/progressions/fetch",
     *     name="innova_path_progressions_fetch",
     *     methods={"GET"}
     * )
     * @EXT\ParamConverter(
     *     "path",
     *     class="InnovaPathBundle:Path\Path",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param Path    $path
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function progressionsFetchAction(Path $path, Request $request)
    {
        $node = $path->getResourceNode();

        if (!$this->authorization->isGranted('EDIT', new ResourceCollection([$node]))) {
            throw new AccessDeniedException('Operation "EDIT" cannot be done on object'.get_class($node));
        }

        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['resourceNode'] = [$node->getUuid()];
        $data = $this->finder->search(ResourceUserEvaluation::class, $params, [Options::SERIALIZE_MINIMAL]);

        return new JsonResponse($data, 200);
    }

    /**
     * Fetch user progressions for path.
     *
     * @Route(
     *     "/{id}/user/{user}/steps/progression/fetch",
     *     name="innova_path_user_steps_progression_fetch",
     *     methods={"GET"}
     * )
     * @EXT\ParamConverter(
     *     "path",
     *     class="InnovaPathBundle:Path\Path",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "user",
     *     class="ClarolineCoreBundle:User",
     *     options={"mapping": {"user": "uuid"}}
     * )
     *
     * @param Path $path
     * @param User $user
     *
     * @return JsonResponse
     */
    public function userStepsProgressionFetchAction(Path $path, User $user)
    {
        $node = $path->getResourceNode();

        if (!$this->authorization->isGranted('EDIT', new ResourceCollection([$node]))) {
            throw new AccessDeniedException('Operation "EDIT" cannot be done on object'.get_class($node));
        }
        $data = $this->userProgressionManager->getStepsProgressionForUser($path, $user);

        if (empty($data)) {
            $data = new \stdClass();
        }

        return new JsonResponse($data, 200);
    }

    public function getName()
    {
        return 'path';
    }
}
