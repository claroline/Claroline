<?php

namespace Claroline\PdfPlayerBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\PdfPlayerBundle\Manager\UserEvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/pdf")
 */
class UserEvaluationController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var SerializerProvider */
    private $serializer;

    /** @var UserEvaluationManager */
    private $userEvaluationManager;

    /**
     * UserEvaluationController constructor.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        SerializerProvider $serializer,
        UserEvaluationManager $userEvaluationManager
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->userEvaluationManager = $userEvaluationManager;
    }

    /**
     * @Route("/{id}/progression/{page}/{total}", name="apiv2_pdf_progression_update", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\ParamConverter("pdf", class="Claroline\CoreBundle\Entity\Resource\File", options={"mapping": {"id": "uuid"}})
     *
     * @param int $page
     * @param int $total
     *
     * @return JsonResponse
     */
    public function updateAction(User $user, File $pdf, $page, $total)
    {
        if (!$this->authorization->isGranted('OPEN', new ResourceCollection([$pdf->getResourceNode()]))) {
            throw new AccessDeniedException('Operation "OPEN" cannot be done on object'.get_class($pdf->getResourceNode()));
        }

        $this->userEvaluationManager->update($pdf->getResourceNode(), $user, intval($page), intval($total));

        $resourceUserEvaluation = $this->userEvaluationManager->getResourceUserEvaluation($pdf->getResourceNode(), $user);

        return new JsonResponse([
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [Options::SERIALIZE_MINIMAL]),
        ]);
    }
}
