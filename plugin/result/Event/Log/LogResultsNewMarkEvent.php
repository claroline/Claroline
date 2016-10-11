<?php

namespace Claroline\ResultBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\ResultBundle\Entity\Mark;

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 9/2/16
 */
class LogResultsNewMarkEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_result-mark_added';

    /**
     * @param Mark $mark
     * @param null $oldMark
     */
    public function __construct(Mark $mark, $oldMark = null)
    {
        $result = $mark->getResult();
        $receiverUser = $mark->getUser();
        $details = [
            'receiverUser' => [
                'firstName' => $receiverUser->getFirstName(),
                'lastName' => $receiverUser->getLastName(),
                'username' => $receiverUser->getUsername(),
            ],
            'result' => $mark->getValue(),
            'update' => $oldMark !== null,
            'mark' => [
                'id' => $mark->getId(),
                'value' => $mark->getValue(),
                'oldValue' => $oldMark,
            ],
        ];
        parent::__construct($result->getResourceNode(), $details);
        $this->setReceiver($receiverUser);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [LogGenericEvent::DISPLAYED_WORKSPACE];
    }
}
