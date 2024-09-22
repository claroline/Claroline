<?php

namespace Claroline\EvaluationBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\EvaluationBundle\Entity\Skill\SkillsFramework;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/skills_framework', name: 'apiv2_skills_framework_')]
class SkillsFrameworkController extends AbstractCrudController
{
    public static function getName(): string
    {
        return 'skills_framework';
    }

    public static function getClass(): string
    {
        return SkillsFramework::class;
    }

    /**
     * @EXT\ParamConverter("workspace", class="Claroline\EvaluationBundle\Entity\Skill\SkillsFramework", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/copy/{id}', name: 'copy', methods: ['POST'])]
    public function copyAction(SkillsFramework $skillsFramework): JsonResponse
    {
        $copy = $this->crud->copy($skillsFramework);

        return new JsonResponse($this->serializer->serialize($copy), 201);
    }

    #[Route(path: '/import', name: 'import', methods: ['POST'])]
    public function importAction(Request $request): JsonResponse
    {
        return new JsonResponse(null, 201);
    }
}
