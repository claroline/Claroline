<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Event\Log;

use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogKeywordEditEvent extends LogGenericEvent
{
    const ACTION = 'clacoformbundle-keyword-edit';

    public function __construct(Keyword $keyword)
    {
        $clacoForm = $keyword->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $details = [];
        $details['id'] = $keyword->getId();
        $details['name'] = $keyword->getName();
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();
        parent::__construct(self::ACTION, $details, null, null, $resourceNode);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
