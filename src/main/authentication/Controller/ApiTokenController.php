<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/apitoken', name: 'apiv2_apitoken_')]
class ApiTokenController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return ApiToken::class;
    }

    public static function getName(): string
    {
        return 'apitoken';
    }

    /**
     * List ApiTokens for the currently logged user.
     */
    #[Route(path: '/current', name: 'list_current', methods: ['GET'])]
    public function listForCurrentUserAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $finderQuery->addFilters([
            'user' => $this->tokenStorage->getToken()?->getUser()->getUuid(),
        ]);

        $tokens = $this->crud->search(ApiToken::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $tokens->toResponse();
    }

    /**
     * List ApiTokens for the specified User.
     */
    #[Route(path: '/user/{userId}', name: 'list_user', methods: ['GET'])]
    public function listByUserAction(
        #[MapEntity(mapping: ['userId' => 'uuid'])]
        User $user,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('EDIT', $user, [], true);

        $finderQuery->addFilters([
            'user' => $user->getUuid(),
        ]);

        $tokens = $this->crud->search(ApiToken::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $tokens->toResponse();
    }
}
