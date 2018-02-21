<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\Facet\Facet;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/profile")
 *
 * There is a thing named Link which work through an event. See older version on github so we can
 * add it again
 */
class ProfileController extends AbstractApiController
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var ProfileSerializer */
    private $serializer;

    /**
     * ProfileController constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "crud"       = @DI\Inject("claroline.api.crud"),
     *     "serializer" = @DI\Inject("claroline.serializer.profile")
     * })
     *
     * @param ObjectManager     $om
     * @param Crud              $crud
     * @param ProfileSerializer $serializer
     */
    public function __construct(
        ObjectManager $om,
        Crud $crud,
        ProfileSerializer $serializer
    ) {
        $this->om = $om;
        $this->crud = $crud;
        $this->serializer = $serializer;
    }

    public function getName()
    {
        return 'profile';
    }

    /**
     * Gets the profile configuration for the current platform.
     *
     * @EXT\Route("", name="apiv2_profile_get")
     * @EXT\Method("GET")
     */
    public function getAction()
    {
        return new JsonResponse(
            $this->serializer->serialize()
        );
    }

    /**
     * Updates the profile configuration for the current platform.
     *
     * @EXT\Route("", name="apiv2_profile_update")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $formData = $this->decodeRequest($request);

        // dump current profile configuration (to know what to remove later)
        /** @var Facet[] $facets */
        $facets = $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')->findAll();

        $this->om->startFlushSuite();

        // updates facets data
        $updatedFacets = [];
        foreach ($formData as $facetData) {
            $updated = $this->crud->update(
                'Claroline\CoreBundle\Entity\Facet\Facet',
                $facetData,
                [Options::DEEP_DESERIALIZE]
            );
            $updatedFacets[$updated->getId()] = $updated;
        }

        // removes deleted facets
        foreach ($facets as $facet) {
            if (empty($updatedFacets[$facet->getId()])) {
                $this->crud->delete($facet);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(
            $this->serializer->serialize()
        );
    }
}
