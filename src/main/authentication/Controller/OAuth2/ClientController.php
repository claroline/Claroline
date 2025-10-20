<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Controller\OAuth2;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Entity\OAuthClient;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/oauth_client', name: 'apiv2_authentication_oauth_client_')]
class ClientController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return OAuthClient::class;
    }

    public static function getName(): string
    {
        return 'oauth_client';
    }
}
