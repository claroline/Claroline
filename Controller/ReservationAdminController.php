<?php

namespace FormaLibre\ReservationBundle\Controller;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\ReservationBundle\Entity\Resource;
use FormaLibre\ReservationBundle\Entity\ResourceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ReservationAdminController extends Controller
{
    private $em;
    private $om;
    private $formFactory;
    private $router;
    private $request;
    private $resourceTypeRepo;

    /**
     * @DI\InjectParams({
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager"),
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *      "router"      = @DI\Inject("router"),
     *      "request"     = @DI\Inject("request")
     * })
     */
    public function __construct(EntityManager $em, FormFactory $formFactory, ObjectManager $om, RouterInterface $router, Request $request)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->router = $router;
        $this->request = $request;
        $this->resourceTypeRepo = $this->om->getRepository('FormaLibreReservationBundle:ResourceType');
    }

    /**
     * @EXT\Route("/admin/", name="formalibre_reservation_admin_index")
     */
    public function indexAction()
    {
        $resourcesType = $this->resourceTypeRepo->findAll();

        return $this->render('FormaLibreReservationBundle::admin/index.html.twig', array('resourcesType' => $resourcesType));
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
                'error' => 'empty_string'
            ));
        }

        if ($this->resourceTypeRepo->findOneBy(array('name' => $name))) {
            return new jsonResponse(array(
                'error' => 'resource_type_exists'
            ));
        }

        $resourceType = new ResourceType();
        $resourceType->setName($name);
        $this->em->persist($resourceType);
        $this->em->flush();

        return new JsonResponse(array('id' => $resourceType->getId()));
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
                'id' => $resourceType->getId()
            ]);
        }

        return $this->render('FormaLibreReservationBundle::admin/resourceTypeForm.html.twig', [
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_modify_resource_type_name', [ 'id' => $resourceType->getId() ])
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
                    'name' => $resource->getName()
                ]
            ]);
        }

        return $this->render('FormaLibreReservationBundle::admin/resourceForm.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_add_new_resource', array('id' => $resourceType->getId())),
            'editMode' => false
        ));
    }

    /**
     * @EXT\Route(
     *      "/modify/resource/{id}",
     *      name="formalibre_modification_resource",
     *      options={"expose"=true}
     * )
     */
    public function modifyResourceAction(Resource $resource)
    {
        $formType = $this->get('formalibre.form.resource');

        $form = $this->createForm($formType, $resource);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->em->flush();

            return new JsonResponse([
                'id' => $resource->getId(),
                'name' => $resource->getName()
            ]);
        }

        return $this->render('FormaLibreReservationBundle::admin/resourceForm.html.twig', array(
            'resource' => $resource,
            'form' => $form->createView(),
            'action' => $this->router->generate('formalibre_modification_resource', ['id' => $resource->getId()]),
            'editMode' => true
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
        $this->em->remove($resource);
        $this->em->flush();

        return new JsonResponse();
    }
}