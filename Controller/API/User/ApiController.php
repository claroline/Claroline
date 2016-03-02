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
use JMS\Serializer\SerializationContext;

class ApiController extends Controller
{
    /**
     * @Route("/connected_user")
     */
    public function connectedUserAction()
    {
        /** @var \Symfony\Component\Security\Core\SecurityContext $securityContext */
        $tokenStorage  = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();

        var_dump($token->getRoles());

        if ($token) {
            $user = $token->getUser();
            $context = new SerializationContext();
            $context->setGroups('api_user');
            $data = $this->container->get('serializer')->serialize($user, 'json', $context);
            $users = json_decode($data);

            return new JsonResponse($users);
        }

        throw new \Exception('No security token.');
    }

    /**
     * @Route("/connected_roles")
     */
    public function connectedRoles()
    {
        /** @var \Symfony\Component\Security\Core\SecurityContext $securityContext */
        $tokenStorage  = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();

        if ($token) {
            $roles = $token->getRoles();
            $context = new SerializationContext();
            $context->setGroups('api_user');
            $data = $this->container->get('serializer')->serialize($roles, 'json');
            $roles = json_decode($data);

            return new JsonResponse($roles);
        }

        throw new \Exception('No security token.');
    }
}
