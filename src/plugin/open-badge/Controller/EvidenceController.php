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
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/badge_evidence', name: 'apiv2_badge_evidence_')]
class EvidenceController extends AbstractCrudController
{
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

    #[Route(path: '/assertion/{assertion}', name: 'create_at', methods: ['POST'])]
    public function createAtAction(
        Request $request,
        #[MapEntity(mapping: ['assertion' => 'uuid'])]
        Assertion $assertion
    ): JsonResponse {
        $object = $this->crud->create(Evidence::class, $this->decodeRequest($request));
        $object->setAssertion($assertion);

        $this->om->persist($object);
        $this->om->flush();

        return new JsonResponse(
            $this->serializer->serialize($object),
            201
        );
    }
}
