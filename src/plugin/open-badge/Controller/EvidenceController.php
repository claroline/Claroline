<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Controller;

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
 * @Route("/badge_evidence", name="apiv2_badge_evidence_")
 */
class EvidenceController extends AbstractCrudController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getClass(): string
    {
        return Evidence::class;
    }

    public static function getName(): string
    {
        return 'badge_evidence';
    }

    public function getIgnore(): array
    {
        return ['get', 'create', 'update', 'list'];
    }

    /**
     * @Route("/assertion/{assertion}", name="create_at", methods={"POST"})
     *
     * @EXT\ParamConverter("assertion", class="Claroline\OpenBadgeBundle\Entity\Assertion", options={"mapping": {"assertion": "uuid"}})
     */
    public function createAtAction(Request $request, Assertion $assertion): JsonResponse
    {
        $object = $this->crud->create($this->getClass(), $this->decodeRequest($request));
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
