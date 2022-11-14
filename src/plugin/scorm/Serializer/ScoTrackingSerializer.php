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
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ScormBundle\Entity\ScoTracking;

class ScoTrackingSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ScoSerializer */
    private $scoSerializer;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        ObjectManager $om,
        ScoSerializer $scoSerializer,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->scoSerializer = $scoSerializer;
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'sco_tracking';
    }

    public function serialize(ScoTracking $scoTracking): array
    {
        $sco = $scoTracking->getSco();
        $user = $scoTracking->getUser();

        // grab info from ResourceUserEvaluation
        $resourceUserEvaluation = $this->om->getRepository(ResourceUserEvaluation::class)->findOneBy([
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
