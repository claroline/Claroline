<?php

namespace FormaLibre\ReservationBundle\Controller;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Entity\Resource;
use FormaLibre\ReservationBundle\Entity\ResourceType;
use FormaLibre\ReservationBundle\Form\ImportResourcesViaCsvFileType;
use FormaLibre\ReservationBundle\Manager\ReservationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_reservation_tool')")
 */
class ReservationAdminController extends Controller
{
    private $em;
    private $om;
    private $router;
    private $request;
    private $reservationManager;
    private $eventDispatcher;
    private $resourceTypeRepo;
    private $resourceRightsRepo;
    private $roleRepo;

    /**
     * @DI\InjectParams({
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager"),
     *      "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *      "router"      = @DI\Inject("router"),
     *      "request"     = @DI\Inject("request"),
     *      "reservationManager" = @DI\Inject("formalibre.manager.reservation_manager"),
     *      "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        EntityManager $em,
        ObjectManager $om,
        RouterInterface $router,
        Request $request,
        ReservationManager $reservationManager,
        StrictDispatcher $eventDispatcher
    ) {
        $this->em = $em;
        $this->om = $om;
        $this->router = $router;
        $this->request = $request;
        $this->reservationManager = $reservationManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->resourceTypeRepo = $this->om->getRepository('FormaLibreReservationBundle:ResourceType');
        $this->resourceRightsRepo = $this->om->getRepository('FormaLibreReservationBundle:ResourceRights');
        $this->roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');
    }

    /**
     * @EXT\Route(
     *      "/admin",
     *      name="formalibre_reservation_admin_index"
     * )
     *
     * @EXT\Template("FormaLibreReservationBundle:Admin:index.html.twig")
     */
    public function indexAction()
    {
        return [
            'resourcesType' => $this->resourceTypeRepo->findAll(),
            'resourcesRights' => $this->resourceRightsRepo->findAll(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/add/resource-type/{name}",
     *     name="formalibre_add_new_resource_type",
     *     defaults={"name"=""},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Method("POST")
     */
    public function addNewResourceTypeAction($name = '')
    {
        if (empty($name)) {
            return new jsonResponse(array(
                'error' => 'empty_string',
            ));
        }

        if ($this->resourceTypeRepo->findOneBy(array('name' => $name))) {
            return new jsonResponse(array(
                'error' => 'resource_type_exists',
            ));
        }

        $resourceType = new ResourceType();
        $resourceType->setName($name);
        $this->em->persist($resourceType);
        $this->em->flush();

        return new JsonResponse(array(
            'id' => $resourceType->getId(),
            'name' => $resourceType->getName(),
        ));
    }

    /**
     * @EXT\Route(
     *      "/change/resource-type/{id}",
     *      name="formalibre_modify_resource_type_name",
     *      options={"expose"=true}
     * )
     */
    public function changeResourceTypeNameAction(ResourceType $resourceType)
    {
        $formType = $this->get('formalibre.form.resourceType');
        $form = $this->createForm($formType, $resourceType);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->em->flush();

            return new JsonResponse([
                'name' => $resourceType->getName(),
                'id' => $resourceType->getId(),
            ]);
        }

        return $this->render('FormaLibreReservationBundle:Admin:resourceTypeForm.html.twig', [
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_modify_resource_type_name', ['id' => $resourceType->getId()]),
        ]);
    }

    /**
     * @EXT\Route(
     *     "/delete/resource-type/{id}",
     *     name="formalibre_delete_resource_type",
     *     options={"expose"=true}
     * )
     */
    public function deleteResourceTypeAction(ResourceType $resourceType)
    {
        // We have to manually delete the events because doctrine doesn't remove them even if there is a cascade parameter (@see Reservation Entity)
        $this->reservationManager->deleteEventsBoundToResourcesType($resourceType);

        $this->em->remove($resourceType);
        $this->em->flush();

        return new JsonResponse();
    }

/**
 * @EXT\Route(
 *     "/add/resource/{id}",
 *     name="formalibre_add_new_resource",
 *     options={"expose"=true}
 * )
 */
    //The resourceRights are handled by the updateResourceRolesAction() action. This function is executed by an ajax query -> See Resources/public/js/admin.js updateResourceRoles() method. We do this like that for a better form design and user experience
    public function addResourceAction(ResourceType $resourceType)
    {
        $formType = $this->get('formalibre.form.resource');
        $form = $this->createForm($formType, new Resource());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $resource = $form->getData();
            $resource->setResourceType($resourceType);

            $this->em->persist($resource);
            $this->em->flush();

            return new JsonResponse([
                'resourceTypeId' => $resourceType->getId(),
                'resource' => [
                    'id' => $resource->getId(),
                    'name' => $resource->getName(),
                ],
            ]);
        }

        return $this->render('FormaLibreReservationBundle:Admin:resourceForm.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_add_new_resource', array('id' => $resourceType->getId())),
            'editMode' => false,
            'roles' => $this->roleRepo->findBy(['type' => 1]),
        ));
    }

/**
 * @EXT\Route(
 *      "/modify/resource/{id}",
 *      name="formalibre_modification_resource",
 *      options={"expose"=true}
 * )
 */
    //The resourceRights are handled by the updateResourceRolesAction() action. This function is executed by an ajax query -> See Resources/public/js/admin.js updateResourceRoles() method. We do this like that for a better form design and user experience
    public function modifyResourceAction(Resource $resource)
    {
        $formType = $this->get('formalibre.form.resource');

        $form = $this->createForm($formType, $resource);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->em->flush();

            return new JsonResponse([
                'id' => $resource->getId(),
                'name' => $resource->getName(),
            ]);
        }

        $roles = $this->roleRepo->findBy(['type' => 1]);
        $resourcesRights = $this->resourceRightsRepo->findBy(['resource' => $resource]);

        $resourcesRightsArray = [];
        foreach ($resourcesRights as $resourceRights) {
            $resourcesRightsArray[$resourceRights->getRole()->getId()] = $resourceRights->getMask();
        }

        return $this->render('FormaLibreReservationBundle:Admin:resourceForm.html.twig', array(
            'resource' => $resource,
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_modification_resource', ['id' => $resource->getId()]),
            'editMode' => true,
            'roles' => $roles,
            'resourcesRights' => $resourcesRightsArray,
        ));
    }

    /**
     * @EXT\Route(
     *      "/delete/resource/{id}",
     *      name="formalibre_delete_resource",
     *      options={"expose"=true}
     * )
     */
    public function deleteResourceAction(Resource $resource)
    {
        // We have to manually delete the events because doctrine doesn't remove them even if there is a cascade parameter (@see Reservation Entity)
        $this->reservationManager->deleteEventsBoundToResource($resource);

        $this->em->remove($resource);
        $this->em->flush();

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *      "/update/resource/{id}/roles/{rolesList}",
     *      defaults={"rolesList"=""},
     *      name="formalibre_reservation_update_resource_roles",
     *      options={"expose"=true}
     * )
     */
    public function updateResourceRolesAction(Resource $resource, $rolesList = '')
    {
        $tempMaskByRole = explode(',', $rolesList);

        $maskByRole = array();

        foreach ($tempMaskByRole as $oneMaskByRole) {
            $insideOneMaskByRole = explode(':', $oneMaskByRole);
            $maskByRole[intval($insideOneMaskByRole[0])] = intval($insideOneMaskByRole[1]);
        }
        $roles = $this->roleRepo->findBy(['type' => 1]);

        foreach ($roles as $role) {
            $resourceRights = $this->reservationManager->getResourceRightsByRoleAndResource($resource, $role);

            //If it's the role admin, set the mask to the max
            if ($role->getName() === 'ROLE_ADMIN') {
                $resourceRights->setMask(7);
            } else {
                $mask = array_key_exists($role->getId(), $maskByRole) ? $maskByRole[$role->getId()] : 0;
                $resourceRights->setMask($mask);
            }
        }

        $this->em->flush();

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *      "/import",
     *      name="formalibre_reservation_import_resources_form",
     *      options={"expose"=true}
     * )
     */
    public function importResourcesModalFormAction()
    {
        $form = $this->createForm(new ImportResourcesViaCsvFileType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $this->importResourcesAction($form->get('file')->getData());

            return new JsonResponse($data);
        }

        return $this->render('FormaLibreReservationBundle:Admin:importForm.html.twig', [
                'form' => $form->createView(),
                'action' => $this->generateUrl('formalibre_reservation_import_resources_form'),
            ]
        );
    }

    public function importResourcesAction($file)
    {
        $file = new \SplFileObject($file->getPathname());
        $reader = new CsvReader($file);
        $reader->setHeaderRowNumber(0);

        $data = [
            'resourcesTypes' => [],
            'resources' => [],
        ];

        foreach ($reader as $row) {
            $resourceTypeName = ucfirst($row['resource_type']);
            $resourceName = ucfirst($row['name']);
            $maxTimeReservation = $row['max_time_reservation'];
            $description = ucfirst($row['description']);
            $localisation = ucfirst($row['localisation']);
            $quantity = $row['quantity'];
            $color = empty($row['color']) ? '#3a87ad' : $row['color'];

            $resourceType = $this->resourceTypeRepo->findOneBy(['name' => $resourceTypeName]);
            if (!$resourceType) {
                $resourceType = new ResourceType();
                $resourceType->setName($resourceTypeName);
                $this->em->persist($resourceType);
                $this->em->flush();

                $data['resourcesTypes'][] = [
                    'id' => $resourceType->getId(),
                    'name' => $resourceType->getName(),
                ];
            }

            $resource = new Resource();
            $resource
                ->setResourceType($resourceType)
                ->setName($resourceName)
                ->setMaxTimeReservation($maxTimeReservation)
                ->setDescription($description)
                ->setLocalisation($localisation)
                ->setQuantity($quantity)
                ->setColor($color)
            ;
            $this->em->persist($resource);
            $this->em->flush();

            $data['resources'][] = [
                'resourceTypeId' => $resourceType->getId(),
                'resource' => [
                    'id' => $resource->getId(),
                    'name' => $resourceName,

                ],
            ];
        }

        return $data;
    }

    /**
     * @EXT\Route(
     *      "/export",
     *      name="formalibre_reservation_export_resources",
     *      options={"expose"=true}
     * )
     */
    public function exportResourcesAction()
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="resources.csv";');

        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'resource_type',
            'name',
            'max_time_reservation',
            'description',
            'localisation',
            'quantity',
            'color',
        ], ',', '"');

        $resourcesTypes = $this->resourceTypeRepo->findAll();
        foreach ($resourcesTypes as $resourceType) {
            foreach ($resourceType->getResources() as $resource) {
                $data = [
                    $resourceType->getName(),
                    $resource->getName(),
                    $resource->getMaxTimeReservation(),
                    $resource->getDescription(),
                    $resource->getLocalisation(),
                    $resource->getQuantity(),
                    $resource->getColor(),
                ];

                fputcsv($file, $data, ',', '"');
            }
        }

        return new Response();
    }
}
