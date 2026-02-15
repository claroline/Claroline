<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\ScormBundle\Entity\ScoTracking;

class ScoTrackingSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly ScoSerializer $scoSerializer,
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function getName(): string
    {
        return 'sco_tracking';
    }

    public function getClass(): string
    {
        return ScoTracking::class;
    }

    public function serialize(ScoTracking $scoTracking): array
    {
        $sco = $scoTracking->getSco();
        $user = $scoTracking->getUser();

        // grab info from ResourceUserEvaluation
        $resourceUserEvaluation = $this->om->getRepository(ResourceEvaluation::class)->findOneBy([
            'user' => $user,
            'resourceNode' => $sco->getScorm()->getResourceNode(),
        ]);

        return [
            'id' => $scoTracking->getUuid(),
            'sco' => empty($sco) ? null : $this->scoSerializer->serialize($sco),
            'user' => empty($user) ? null : $this->userSerializer->serialize($user, [Options::SERIALIZE_MINIMAL]),
            'scoreRaw' => $scoTracking->getScoreRaw(),
            'scoreMin' => $scoTracking->getScoreMin(),
            'scoreMax' => $scoTracking->getScoreMax(),
            'scoreScaled' => $scoTracking->getScoreScaled(),
            'lessonStatus' => $scoTracking->getLessonStatus(),
            'completionStatus' => $scoTracking->getCompletionStatus(),
            'sessionTime' => $scoTracking->getSessionTime(),
            'totalTime' => $scoTracking->getFormattedTotalTime(),
            'totalTimeInt' => $scoTracking->getTotalTimeInt(),
            'totalTimeString' => $scoTracking->getTotalTimeString(),
            'entry' => $scoTracking->getEntry(),
            'suspendData' => $scoTracking->getSuspendData(),
            'credit' => $scoTracking->getCredit(),
            'exitMode' => $scoTracking->getExitMode(),
            'lessonLocation' => $scoTracking->getLessonLocation(),
            'lessonMode' => $scoTracking->getLessonMode(),
            'isLocked' => $scoTracking->getIsLocked(),
            'details' => $scoTracking->getDetails(),
            'latestDate' => DateNormalizer::normalize($scoTracking->getLatestDate()),
            'progression' => $scoTracking->getProgression(),
            'attempts' => $resourceUserEvaluation ? $resourceUserEvaluation->getNbAttempts() : null,
            'views' => $resourceUserEvaluation ? $resourceUserEvaluation->getNbOpenings() : null,
        ];
    }
}
