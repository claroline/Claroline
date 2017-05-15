<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 5/15/17
 */

namespace Claroline\CasBundle\Library\Sso;

use BeSimple\SsoAuthBundle\Buzz\AdaptiveClient;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class CasAdaptiveClient extends AdaptiveClient
{
    public function __construct(array $options, $key, PlatformConfigurationHandler $platformConfigHandler)
    {
        // Change dynamically the ssl version for BeSimpleSsoBundle
        $options[$key] = $platformConfigHandler->getParameter('ssl_version_value');
        parent::__construct($options);
    }
}
