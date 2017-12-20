<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API to manage platform parameters.
 *
 * @Route("/parameters")
 */
class ParametersController
{
    /**
     * ParametersController constructor.
     *
     * @DI\InjectParams({
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "serializer" = @DI\Inject("claroline.serializer.parameters")
     * })
     *
     * @param PlatformConfigurationHandler $ch
     * @param ParametersSerializer         $serializer
     */
    public function __construct(
        PlatformConfigurationHandler $ch,
        ParametersSerializer $serializer
    ) {
        $this->ch = $ch;
        $this->serializer = $serializer;
    }

    /**
     * @Route("", name="apiv2_parameters_list")
     * @Method("GET")
     */
    public function listAction()
    {
        return new JsonResponse($this->serializer->serialize());
    }

    /**
     * @Route("", name="apiv2_parameters_update")
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $parameters = $this->serializer->deserialize(json_decode($request->getContent(), true));
        $this->ch->setParameters($parameters);

        return new JsonResponse($this->serializer->serialize());
    }

    /**
     * @Route("/users", name="apiv2_user_parameters_update")
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateUserAction(Request $request)
    {
        $parameters = $this->serializer->deserializeUser(json_decode($request->getContent(), true));
        $this->ch->setParameters($parameters);

        return new JsonResponse($this->serializer->serialize());
    }
}
