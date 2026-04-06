<?php

namespace Claroline\ProjectBundle\Controller;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ProjectBundle\Entity\Annotation;
use Claroline\ProjectBundle\Entity\Milestone;
use Claroline\ProjectBundle\Entity\Submission;
use Claroline\ProjectBundle\Serializer\AnnotationSerializer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Manages annotations on submissions (trainer feedback per milestone in iterative mode).
 */
#[Route(path: '/project')]
class AnnotationController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly AnnotationSerializer $annotationSerializer
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Lists annotations for a submission.
     */
    #[Route(path: '/submission/{id}/annotations', name: 'claro_project_submission_annotations', methods: ['GET'])]
    public function listAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Submission $submission
    ): JsonResponse {
        $this->checkPermission('OPEN', $submission->getProject()->getResourceNode(), [], true);

        $annotations = $this->om->getRepository(Annotation::class)->findBy(
            ['submission' => $submission],
            ['date' => 'ASC']
        );

        return new JsonResponse(array_map(function (Annotation $annotation) {
            return $this->annotationSerializer->serialize($annotation);
        }, $annotations));
    }

    /**
     * Creates an annotation on a submission for a specific milestone.
     */
    #[Route(path: '/submission/{id}/milestone/{milestoneId}/annotation', name: 'claro_project_annotation_create', methods: ['POST'])]
    public function createAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Submission $submission,
        #[MapEntity(mapping: ['milestoneId' => 'uuid'])]
        Milestone $milestone,
        #[CurrentUser]
        User $user,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $submission->getProject()->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);

        $annotation = new Annotation();
        $annotation->setSubmission($submission);
        $annotation->setMilestone($milestone);
        $annotation->setUser($user);
        $annotation->setContent($data['content'] ?? '');
        $annotation->setDate(new \DateTime());

        $this->om->persist($annotation);
        $this->om->flush();

        return new JsonResponse($this->annotationSerializer->serialize($annotation), 201);
    }

    /**
     * Updates an existing annotation.
     */
    #[Route(path: '/annotation/{id}', name: 'claro_project_annotation_update', methods: ['PUT'])]
    public function updateAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Annotation $annotation,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $annotation->getSubmission()->getProject()->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);

        if (isset($data['content'])) {
            $annotation->setContent($data['content']);
        }

        $this->om->persist($annotation);
        $this->om->flush();

        return new JsonResponse($this->annotationSerializer->serialize($annotation));
    }

    /**
     * Deletes an annotation.
     */
    #[Route(path: '/annotation/{id}', name: 'claro_project_annotation_delete', methods: ['DELETE'])]
    public function deleteAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Annotation $annotation
    ): JsonResponse {
        $this->checkPermission('EDIT', $annotation->getSubmission()->getProject()->getResourceNode(), [], true);

        $this->om->remove($annotation);
        $this->om->flush();

        return new JsonResponse(null, 204);
    }
}
