<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller\APINew;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ParametersController
{
    /** @var PlatformConfigurationHandler */
    private $pch;

    /**
     * ParametersController constructor.
     *
     * @DI\InjectParams({
     *     "pch" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param PlatformConfigurationHandler $pch
     */
    public function __construct(PlatformConfigurationHandler $pch)
    {
        $this->pch = $pch;
    }

    /**
     * @Route("paramters/update", name="apiv2_cursus_parameters_update")
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        $this->pch->setParameter('cursus', $parameters);

        return new JsonResponse($parameters, 200);
    }
}
