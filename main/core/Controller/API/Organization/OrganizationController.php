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

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Form\Organization\OrganizationParametersType;
use Claroline\CoreBundle\Form\Organization\OrganizationType;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @NamePrefix("api_")
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
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
    ) {
        $this->formFactory = $formFactory;
        $this->organizationManager = $organizationManager;
        $this->om = $om;
        $this->request = $request;
        $this->apiManager = $apiManager;
    }

    /**
     * @View(serializerGroups={"api_organization_list"})
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
     * @View()
     * @EXT\ParamConverter("organization", class="ClarolineCoreBundle:Organization\Organization",)
     */
    public function deleteOrganizationAction(Organization $organization)
    {
        $this->organizationManager->delete($organization);

        return ['success'];
    }

    /**
     * @View(serializerGroups={"api_organization_tree"})
     */
    public function getOrganizationsAction()
    {
        return $this->organizationManager->getRoots();
    }

    /**
     * @View(serializerGroups={"api_organization_list"})
     */
    public function getOrganizationListAction()
    {
        return $this->organizationManager->getAll();
    }

    /**
     * @View(serializerGroups={"api_organization_list"})
     * @Get("/organization/{organization}/edit/form")
     */
    public function getOrganizationEditFormAction(Organization $organization)
    {
        $formType = new OrganizationParametersType('eofm');
        $formType->enableApi();
        $form = $this->createForm($formType, $organization);
        $options = [
            'serializer_group' => 'api_organization_list',
        ];

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editOrganizationForm.html.twig', $form, $options);
    }

    /**
     * @View(serializerGroups={"api_organization_list"})
     */
    public function putOrganizationAction(Organization $organization)
    {
        $formType = new OrganizationParametersType('eofm');
        $formType->enableApi();
        $form = $this->formFactory->create($formType, $organization);
        $form->submit($this->request);
        $httpCode = 400;

        if ($form->isValid()) {
            $organization = $form->getData();
            $organization = $this->organizationManager->edit($organization);
            $httpCode = 200;
        }

        $options = [
            'http_code' => $httpCode,
            'extra_parameters' => $organization,
            'serializer_group' => 'api_organization_list',
        ];

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:Organization\editLocationForm.html.twig', $form, $options);
    }

    /**
     * @View(serializerGroups={"api_organization_list"})
     * @Get("/organization/{organization}/move/{parent}")
     */
    public function moveOrganizationAction(Organization $organization, $parent)
    {
        $parent = $this->om->getRepository('ClarolineCoreBundle:Organization\Organization')->find($parent);

        return $this->organizationManager->setParent($organization, $parent);
    }
}
