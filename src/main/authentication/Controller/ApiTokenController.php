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
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("apitoken")
 */
class ApiTokenController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass(): string
    {
        return ApiToken::class;
    }

    public function getName(): string
    {
        return 'apitoken';
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $tool = $this->om->getRepository(AdminTool::class)
            ->findOneBy(['name' => 'integration']);

        if (!$this->authorization->isGranted('OPEN', $tool)) {
            // only list tokens of the current token for non admins
            return [
                'user' => $this->tokenStorage->getToken()->getUser()->getUuid(),
            ];
        }

        return [];
    }

    /**
     * @Route("/list/current", name="apiv2_apitoken_list_current", methods={"GET"})
     */
    public function getCurrentAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $options = static::getOptions();

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'user' => $this->tokenStorage->getToken()->getUser()->getUuid(),
        ];

        return new JsonResponse($this->finder->search(
            $this->getClass(),
            $query,
            $options['get'] ?? []
        ));
    }
}
