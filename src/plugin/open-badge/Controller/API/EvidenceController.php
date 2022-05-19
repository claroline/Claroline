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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/evidence")
 */
class EvidenceController extends AbstractCrudController
{
    public function getName()
    {
        return 'badge-evidence';
    }

    /**
     * @Route("/assertion/{assertion}", name="apiv2_evidence_create_at", methods={"POST"})
     * @EXT\ParamConverter("assertion", class="Claroline\OpenBadgeBundle\Entity\Assertion", options={"mapping": {"assertion": "uuid"}})
     *
     * @return JsonResponse
     */
    public function createAtAction(Request $request, Assertion $assertion)
    {
        $object = $this->crud->create(
            $this->getClass(),
            $this->decodeRequest($request),
            []
        );

        $object->setAssertion($assertion);
        $this->om->persist($object);
        $this->om->flush();

        if (is_array($object)) {
            return new JsonResponse($object, 400);
        }

        return new JsonResponse(
            $this->serializer->serialize($object, []),
            201
        );
    }

    public function getClass()
    {
        return Evidence::class;
    }
}
