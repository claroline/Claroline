<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class ParametersController extends FOSRestController
{
    private $request;

    /**
     * @DI\InjectParams({
     *     "request" = @DI\Inject("request"),
     *     "ch"      = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(Request $request, PlatformConfigurationHandler $ch)
    {
        $this->request = $request;
        $this->ch = $ch;
    }

    /**
     * @View()
     */
    public function postParametersAction()
    {
        $data = $this->request->request;

        foreach ($data as $parameter => $value) {
            $this->ch->setParameter($parameter, $value);
        }

        return $data;
    }
}
