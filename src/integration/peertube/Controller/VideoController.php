<?php

namespace Claroline\PeerTubeBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\EvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/peertube_video")
 */
class VideoController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var EvaluationManager */
    private $evaluationManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
        $this->evaluationManager = $evaluationManager;
    }

    public function getClass(): string
    {
        return Video::class;
    }

    public function getName(): string
    {
        return 'peertube_video';
    }

    public function getIgnore(): array
    {
        // we only keep update method
        return ['list', 'get', 'create', 'deleteBulk', 'copyBulk', 'exist', 'find'];
    }

    /**
     * @Route("/{id}/progression/{currentTime}/{totalTime}", name="apiv2_peertube_video_progression_update", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\ParamConverter("video", class="Claroline\PeerTubeBundle\Entity\Video", options={"mapping": {"id": "uuid"}})
     */
    public function updateProgressionAction(User $user, Video $video, $currentTime, $totalTime): JsonResponse
    {
        $this->checkPermission('OPEN', $video->getResourceNode(), [], true);

        $this->evaluationManager->update($video->getResourceNode(), $user, floatval($currentTime), floatval($totalTime));

        $resourceUserEvaluation = $this->evaluationManager->getResourceUserEvaluation($video->getResourceNode(), $user);

        return new JsonResponse([
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
        ]);
    }
}
