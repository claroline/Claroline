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
class LogResultsDeleteMarkEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_result-mark_deleted';

    /**
     * @param Mark $mark
     */
    public function __construct(Mark $mark)
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
            'mark' => [
                'id' => $mark->getId(),
                'value' => $mark->getValue(),
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
