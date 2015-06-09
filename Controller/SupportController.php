<?php

namespace FormaLibre\SupportBundle\Controller;

use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenTool('formalibre_support_tool')")
 */
class SupportController extends Controller
{
    private $formFactory;
    private $request;
    private $supportManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "supportManager" = @DI\Inject("formalibre.manager.support_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        SupportManager $supportManager
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->supportManager = $supportManager;
    }

    /**
     * @EXT\Route(
     *     "/support/index",
     *     name="formalibre_support_index",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function supportIndexAction()
    {
        return array();
    }
}
