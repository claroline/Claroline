<?php

namespace Claroline\EvaluationBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\EvaluationBundle\Entity\Skill\SkillsFramework;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/skills_framework")
 */
class SkillsFrameworkController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'skills_framework';
    }

    public function getClass(): string
    {
        return SkillsFramework::class;
    }

    /**
     * @Route("/copy/{id}", methods={"POST"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\EvaluationBundle\Entity\Skill\SkillsFramework", options={"mapping": {"id": "uuid"}})
     */
    public function copyAction(SkillsFramework $skillsFramework): JsonResponse
    {
        $copy = $this->crud->copy($skillsFramework);

        return new JsonResponse($this->serializer->serialize($copy), 201);
    }

    /**
     * @Route("/import", methods={"POST"})
     */
    public function importAction(Request $request): JsonResponse
    {
        return new JsonResponse(null, 201);
    }
}
