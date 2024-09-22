<?php

namespace Claroline\PdfPlayerBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\User;
use Claroline\PdfPlayerBundle\Manager\EvaluationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/pdf')]
class EvaluationController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly SerializerProvider $serializer,
        private readonly EvaluationManager $evaluationManager
    ) {
    }

    #[Route(path: '/{id}/progression/{page}/{total}', name: 'apiv2_pdf_progression_update', methods: ['PUT'])]
    public function updateAction(#[CurrentUser] ?User $user, #[MapEntity(class: 'Claroline\CoreBundle\Entity\Resource\File', mapping: ['id' => 'uuid'])]
    File $pdf, int $page, int $total): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(null, 204);
        }

        if (!$this->authorization->isGranted('OPEN', $pdf->getResourceNode())) {
            throw new AccessDeniedException('Operation "OPEN" cannot be done on object'.get_class($pdf->getResourceNode()));
        }

        $this->evaluationManager->update($pdf->getResourceNode(), $user, $page, $total);

        $resourceUserEvaluation = $this->evaluationManager->getResourceUserEvaluation($pdf->getResourceNode(), $user);

        return new JsonResponse([
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [Options::SERIALIZE_MINIMAL]),
        ]);
    }
}
