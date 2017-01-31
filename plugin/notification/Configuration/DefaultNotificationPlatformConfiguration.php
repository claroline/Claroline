<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\NotificationBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.configuration")
 */
class DefaultNotificationPlatformConfiguration implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        return [
            'is_notification_active' => true,
        ];
    }
}
