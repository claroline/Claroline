<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Controller\API;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/evidence")
 */
class EvidenceController extends AbstractCrudController
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
        return Evidence::class;
    }

    public function getName(): string
    {
        return 'badge-evidence';
    }

    /**
     * @Route("/assertion/{assertion}", name="apiv2_evidence_create_at", methods={"POST"})
     * @EXT\ParamConverter("assertion", class="Claroline\OpenBadgeBundle\Entity\Assertion", options={"mapping": {"assertion": "uuid"}})
     */
    public function createAtAction(Request $request, Assertion $assertion): JsonResponse
    {
        $object = $this->crud->create($this->getClass(), $this->decodeRequest($request), [Crud::THROW_EXCEPTION]);
        $object->setAssertion($assertion);

        $this->om->persist($object);
        $this->om->flush();

        return new JsonResponse(
            $this->serializer->serialize($object),
            201
        );
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            // anonymous cannot have badges
            throw new AccessDeniedException();
        }

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            return [
                'recipient' => $this->tokenStorage->getToken()->getUser()->getUuid(),
            ];
        }

        return [];
    }
}
