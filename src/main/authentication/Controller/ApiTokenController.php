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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: 'apitoken', name: 'apiv2_apitoken_')]
class ApiTokenController extends AbstractCrudController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getClass(): string
    {
        return ApiToken::class;
    }

    public static function getName(): string
    {
        return 'apitoken';
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            // only list tokens of the current token for non admins
            return [
                'user' => $this->tokenStorage->getToken()?->getUser()->getUuid(),
            ];
        }

        return [];
    }

    #[Route(path: '/list/current', name: 'list_current', methods: ['GET'])]
    public function getCurrentAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $options = static::getOptions();

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'user' => $this->tokenStorage->getToken()?->getUser()->getUuid(),
        ];

        return new JsonResponse($this->crud->list(
            static::getClass(),
            $query,
            $options['get'] ?? []
        ));
    }
}
