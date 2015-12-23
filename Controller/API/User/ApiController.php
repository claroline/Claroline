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

use FOS\RestBundle\Util\Codes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations\View;

class ApiController extends Controller
{
    /**
     * @Route("/connected_user")
     * @View(serializerGroups={"api"})
     */
    public function connectedUserAction()
    {
        /** @var \Symfony\Component\Security\Core\SecurityContext $securityContext */
        $tokenStorage    = $this->container->get('security.token_storage');
        $securityToken   = $tokenStorage->getToken();

        if (null !== $securityToken) {
            return $securityToken->getUser();
        }

        throw new \Exception('No user connected');
    }
}
