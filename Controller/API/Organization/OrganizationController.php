<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Organization;

use Claroline\CoreBundle\Manager\ApiManager;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\Controller\FOSRestController;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Form\Organization\OrganizationType;
use Claroline\CoreBundle\Form\Organization\OrganizationParametersType;
use Claroline\CoreBundle\Form\Organization\OrganizationNameType;

/**
 * @NamePrefix("api_")
 */
class OrganizationController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "organizationManager" = @DI\Inject("claroline.manager.organization.organization_manager"),
     *     "request"             = @DI\Inject("request"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "apiManager"          = @DI\Inject("claroline.manager.api_manager")
     * })
     */
    public function __construct(
        FormFactory          $formFactory,
        OrganizationManager  $organizationManager,
        ObjectManager        $om,
        Request              $request,
        ApiManager           $apiManager
    )
    {
        $this->formFactory         = $formFactory;
        $this->organizationManager = $organizationManager;
        $this->om                  = $om;
        $this->request             = $request;
        $this->apiManager          = $apiManager;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Creates an organization",
     *     views = {"organization"},
     *     input="Claroline\CoreBundle\Form\OrganizationType"
     * )
     */
    public function postOrganizationAction()
    {
        $organizationType = new OrganizationType();
        $organizationType->enableApi();
        $form = $this->formFactory->create($organizationType, new Organization());
        $form->submit($this->request);

        if ($form->isValid()) {
            $organization = $form->getData();
            $organization = $this->organizationManager->create($organization);

            return $organization;
        }

        return $form;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Update an organization name",
     *     views = {"organization"},
     *     input="Claroline\CoreBundle\Form\OrganizationType"
     * )
     * @EXT\ParamConverter("organization", class="ClarolineCoreBundle:Organization\Organization")
     */
    public function putOrganizationNameAction(Organization $organization)
    {
        $organizationNameType = new OrganizationNameType();
        $organizationNameType->enableApi();
        $form = $this->formFactory->create($organizationNameType, $organization);
        $form->submit($this->request);
        //form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->organizationManager->edit($form->getData());

            return array('success');
        }

        return $form;
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Removes an organization",
     *     section="organization",
     *     views = {"api"}
     * )
     * @EXT\ParamConverter("organization", class="ClarolineCoreBundle:Organization\Organization",)
     */
    public function deleteOrganizationAction(Organization $organization)
    {
        $this->organizationManager->delete($organization);

        return array('success');
    }

        /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the organizations list",
     *     views = {"organization"}
     * )
     */
    public function getOrganizationsAction()
    {
        return $this->organizationManager->getRoots();
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the location edition form",
     *     views = {"location"}
     * )
     */
    public function getEditOrganizationFormAction(Organization $organization)
    {
        $formType = new OrganizationParametersType();
        $formType->enableApi();
        $form = $this->createForm($formType, $organization);

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editOrganizationForm.html.twig', $form);
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Update an organization",
     *     views = {"location"},
     *     input="Claroline\CoreBundle\Form\LocationType"
     * )
     */
    public function putOrganizationAction(Organization $organization)
    {
        $formType = new OrganizationParametersType();
        $formType->enableApi();
        $form = $this->formFactory->create($formType, $organization);
        $form->submit($this->request);
        $httpCode = 400;

        if ($form->isValid()) {
            $organization = $form->getData();
            $organization = $this->organizationManager->edit($organization);
            $httpCode = 200;
        }

        $options = array(
            'http_code' => $httpCode,
            'extra_parameters' => $organization
        );

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editLocationForm.html.twig', $form, $options);
    }
}
