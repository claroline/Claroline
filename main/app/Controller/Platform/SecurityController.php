<?php

namespace Claroline\AppBundle\Controller\Platform;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController
{
    /**
     * Logs a user into the platform (all of the security stuffs are done by symfony internals).
     *
     * @EXT\Route("/login", name="claro_security_login")
     * @EXT\Method("POST")
     *
     * @return JsonResponse
     */
    public function loginAction()
    {
        // Response is returned in the AuthenticationSuccessHandler
        return new JsonResponse();
    }
}
