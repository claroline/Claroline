<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\ReservationBundle\Controller\API;

use Claroline\CoreBundle\Annotations\ApiMeta;
use Claroline\CoreBundle\Controller\APINew\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @ApiMeta(class="FormaLibre\ReservationBundle\Entity\Resource")
 * @Route("/reservationresource")
 */
class ResourceController extends AbstractCrudController
{
    use HasOrganizationsTrait;

    private $apiManager;
    private $resourceRepo;

    /**
     * @DI\InjectParams({
     *     "apiManager" = @DI\Inject("claroline.manager.api_manager"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ApiManager    $apiManager
     * @param ObjectManager $om
     */
    public function __construct(ApiManager $apiManager, ObjectManager $om)
    {
        $this->apiManager = $apiManager;
        $this->resourceRepo = $om->getRepository('FormaLibreReservationBundle:Resource');
    }

    public function getName()
    {
        return 'reservation_resource';
    }

    /**
     * List organizations of the collection.
     *
     * @Route("/{id}/organization")
     * @Method("GET")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listOrganizationsAction($id, Request $request)
    {
        $resource = $this->resourceRepo->findOneBy(['uuid' => $id]);
        $organizations = !empty($resource) ? $resource->getOrganizations() : [];
        $organizationsUuids = array_map(function (Organization $organization) {
            return $organization->getUuid();
        }, $organizations);

        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Organization\Organization', array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['whitelist' => $organizationsUuids]]
            ))
        );
    }

    /**
     * Exports resources.
     *
     * @Route(
     *     "/resources/export",
     *     name="apiv2_reservationresource_export"
     * )
     * @Method("GET")
     *
     * @return StreamedResponse
     */
    public function exportResourcesAction()
    {
        $resources = $this->apiManager->getParametersByUuid('ids', 'FormaLibre\ReservationBundle\Entity\Resource');

        $response = new StreamedResponse(function () use ($resources) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'resourceType.name',
                'id',
                'name',
                'maxTimeReservation',
                'description',
                'localization',
                'quantity',
                'color',
            ], ';', '"');

            foreach ($resources as $resource) {
                if (!empty($resource)) {
                    $resourceType = $resource->getResourceType();
                    $data = [
                        $resourceType->getName(),
                        $resource->getUuid(),
                        $resource->getName(),
                        $resource->getMaxTimeReservation(),
                        $resource->getDescription(),
                        $resource->getLocalisation(),
                        $resource->getQuantity(),
                        $resource->getColor(),
                    ];
                    fputcsv($file, $data, ';', '"');
                }
            }
            fclose($file);
        });
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="resources.csv"');
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Connection', 'close');
        $response->send();

        return $response;
    }
}
