<?php

namespace Claroline\YouTubeBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\YouTubeBundle\Entity\Video;
use Claroline\YouTubeBundle\Manager\EvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/youtube_video')]
class VideoController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly SerializerProvider $serializer,
        private readonly EvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     *
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\ParamConverter("video", class="Claroline\YouTubeBundle\Entity\Video", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/{id}/progression/{currentTime}/{totalTime}', name: 'apiv2_youtube_video_progression_update', methods: ['PUT'])]
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
