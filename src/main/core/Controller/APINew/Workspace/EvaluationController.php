<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Requirements;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace")
 */
class EvaluationController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;
    /** @var EvaluationManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        SerializerProvider $serializer,
        EvaluationManager $manager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * @Route("/{workspace}/evaluations", name="apiv2_workspace_evaluations_list", methods={"GET"})
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listAction(Workspace $workspace, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->finder->search(
            Evaluation::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
            ]])
        ));
    }

    /**
     * @Route("/{workspace}/evaluations/init/{role}", name="apiv2_workspace_evaluations_init", methods={"PUT"})
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     * @EXT\ParamConverter("role", options={"mapping": {"role": "uuid"}})
     */
    public function initializeAction(Workspace $workspace, Role $role): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'EDIT'], $workspace)) {
            throw new AccessDeniedException();
        }

        $users = $this->om->getRepository(User::class)->findByRoles([$role]);

        $this->om->startFlushSuite();
        foreach ($users as $user) {
            // this will automatically create missing evaluation
            $this->manager->getEvaluation($workspace, $user, true);
        }

        // updates resource evaluation with workspace requirements
        // this is only required because in some cases ResourceUserEvaluation were not marked as required
        // when defined in the workspace requirements
        // TODO : to remove in 14.x
        $requirements = $this->om->getRepository(Requirements::class)->findOneBy(['workspace' => $workspace, 'role' => $role]);
        if ($requirements) {
            foreach ($requirements->getResources() as $resource) {
                $this->manager->addRequirementToResourceEvaluationByRole($resource, $role);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }
}
