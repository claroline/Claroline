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
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ScormBundle\Entity\ScoTracking;

class ScoTrackingSerializer
{
    use SerializerTrait;

    /** @var ScoSerializer */
    private $scoSerializer;
    /** @var UserSerializer */
    private $userSerializer;

    /**
     * ScoTrackingSerializer constructor.
     *
     * @param ScoSerializer  $scoSerializer
     * @param UserSerializer $userSerializer
     */
    public function __construct(ScoSerializer $scoSerializer, UserSerializer $userSerializer)
    {
        $this->scoSerializer = $scoSerializer;
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'sco_tracking';
    }

    /**
     * @param ScoTracking $scoTracking
     *
     * @return array
     */
    public function serialize(ScoTracking $scoTracking)
    {
        $sco = $scoTracking->getSco();
        $user = $scoTracking->getUser();

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
        ];
    }
}
