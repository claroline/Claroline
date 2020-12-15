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

class LogClacoFormTemplateEditEvent extends LogGenericEvent
{
    const ACTION = 'clacoformbundle-claco-form-template-edit';

    public function __construct(ClacoForm $clacoForm, $template)
    {
        $resourceNode = $clacoForm->getResourceNode();
        parent::__construct(self::ACTION, ['template' => $template], null, null, $resourceNode);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
