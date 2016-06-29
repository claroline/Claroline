<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\User;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

/**
 * @NamePrefix("api_")
 */
class ApiController extends FOSRestController
{
    /**
     * @View(serializerGroups={"api_user"})
     * @Get("/connected_user", name="get_connected_user", options={ "method_prefix" = false })
     */
    public function connectedUserAction()
    {
        /* @var \Symfony\Component\Security\Core\SecurityContext $securityContext */
        $tokenStorage = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();

        if ($token) {
            return $token->getUser();
        }

        throw new \Exception('No security token.');
    }

    /**
     * @View(serializerGroups={"api_roles"})
     * @Get("/connected_roles", name="get_connected_roles", options={ "method_prefix" = false })
     */
    public function connectedRolesAction()
    {
        /* @var \Symfony\Component\Security\Core\SecurityContext $securityContext */
        $tokenStorage = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();

        if ($token) {
            return $token->getRoles();
        }

        throw new \Exception('No security token.');
    }
}
