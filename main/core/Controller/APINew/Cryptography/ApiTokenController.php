<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Cryptography;

use Claroline\AppBundle\Api\Crud;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Cryptography\ApiToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("apitoken")
 */
class ApiTokenController extends AbstractCrudController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * ResourceController constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route(
     *    "/list/current",
     *    name="apiv2_apitoken_list_current"
     * )
     * @Method("GET")
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function getCurrentAction(Request $request)
    {
        $query = $request->query->all();
        $options = $this->options['list'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $query['hiddenFilters'] = [
            'user' => $this->tokenStorage->getToken()->getUser(),
        ];

        return new JsonResponse($this->finder->search(
            $this->getClass(),
            $query,
            $options
        ));
    }

    public function getClass()
    {
        return ApiToken::class;
    }

    public function getName()
    {
        return 'apitoken';
    }

    public function getOptions()
    {
        return [
            'create' => [Crud::NO_VALIDATE],
        ];
    }
}
