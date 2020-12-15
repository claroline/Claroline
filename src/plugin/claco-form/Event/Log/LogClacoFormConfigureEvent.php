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

use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogClacoFormConfigureEvent extends LogGenericEvent
{
    const ACTION = 'clacoformbundle-claco-form-configure';

    public function __construct(ClacoForm $clacoForm, array $config)
    {
        $resourceNode = $clacoForm->getResourceNode();
        $config['resourceId'] = $clacoForm->getId();
        $config['resourceNodeId'] = $resourceNode->getId();
        $config['resourceName'] = $resourceNode->getName();
        parent::__construct(self::ACTION, $config, null, null, $resourceNode);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
