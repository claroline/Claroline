<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/2/17
 */

namespace Claroline\CasBundle\Controller;

use Claroline\CasBundle\Manager\CasManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CasController.
 *
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class CasController extends Controller
{
    /**
     * @var CasManager
     * @DI\Inject("claroline.manager.cas_manager")
     */
    private $casManager;

    /**
     * @EXT\Route("/admin/parameters/cas/config", name="claro_admin_cas_server_config_form")
     * @EXT\Template("ClarolineCasBundle::serverConfigForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction()
    {
        $form = $this->casManager->getConfigurationForm();

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route("/admin/parameters/cas/config/submit", name="claro_admin_cas_server_config_form_submit")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCasBundle::serverConfigForm.html.twig")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitFormAction(Request $request)
    {
        $form = $this->casManager->getConfigurationForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $config = $form->getData();
            $this->casManager->updateCasParameters($config);

            return $this->redirectToRoute('claro_admin_parameters_third_party_authentication_index');
        }

        return ['form' => $form->createView()];
    }
}
