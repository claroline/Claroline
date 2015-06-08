<?php

namespace FormaLibre\SupportBundle\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenTool('formalibre_support_tool')")
 */
class SupportController extends Controller
{
    /**
     * @EXT\Route(
     *     "/support/index",
     *     name="forma_libre_support_index",
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
