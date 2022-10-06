<?php

namespace Claroline\PdfPlayerBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\PdfPlayerBundle\Manager\EvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/pdf")
 */
class EvaluationController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var SerializerProvider */
    private $serializer;

    /** @var EvaluationManager */
    private $evaluationManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        SerializerProvider $serializer,
        EvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->evaluationManager = $evaluationManager;
    }

    /**
     * @Route("/{id}/progression/{page}/{total}", name="apiv2_pdf_progression_update", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\ParamConverter("pdf", class="Claroline\CoreBundle\Entity\Resource\File", options={"mapping": {"id": "uuid"}})
     *
     * @param int $page
     * @param int $total
     */
    public function updateAction(User $user, File $pdf, $page, $total): JsonResponse
    {
        if (!$this->authorization->isGranted('OPEN', new ResourceCollection([$pdf->getResourceNode()]))) {
            throw new AccessDeniedException('Operation "OPEN" cannot be done on object'.get_class($pdf->getResourceNode()));
        }

        $this->evaluationManager->update($pdf->getResourceNode(), $user, intval($page), intval($total));

        $resourceUserEvaluation = $this->evaluationManager->getResourceUserEvaluation($pdf->getResourceNode(), $user);

        return new JsonResponse([
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [Options::SERIALIZE_MINIMAL]),
        ]);
    }
}
