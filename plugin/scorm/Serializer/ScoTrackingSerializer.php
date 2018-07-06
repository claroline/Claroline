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
use Claroline\ScormBundle\Entity\ScoTracking;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.scorm.sco.tracking")
 * @DI\Tag("claroline.serializer")
 */
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
     * @DI\InjectParams({
     *     "scoSerializer"  = @DI\Inject("claroline.serializer.scorm.sco"),
     *     "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param ScoSerializer  $scoSerializer
     * @param UserSerializer $userSerializer
     */
    public function __construct(ScoSerializer $scoSerializer, UserSerializer $userSerializer)
    {
        $this->scoSerializer = $scoSerializer;
        $this->userSerializer = $userSerializer;
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
            'latestDate' => $scoTracking->getLatestDate() ? $scoTracking->getLatestDate()->format('Y-m-d\TH:i:s') : null,
        ];
    }
}
