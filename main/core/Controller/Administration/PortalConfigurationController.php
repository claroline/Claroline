<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/17/16
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class PortalConfigurationController extends Controller
{
    /**
     * @EXT\Route("/parameters", name="claro_admin_portal_parameters")
     * @EXT\Template("ClarolineCoreBundle:Administration\Portal:index.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $portalManager = $this->get('claroline.manager.portal_manager');
        $data = array(
            'portalResources' => $portalManager->getPortalEnabledResourceTypes(),
        );
        $form = $this->createForm('portal_configuration_form', $data);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $portalResources = $form['portalResources']->getData();
                    $portalManager->setPortalEnabledResourceTypes($portalResources);
                } catch (UnwritableException $e) {
                    $form->addError(
                        new FormError(
                            $this->get('translator')->trans(
                                'unwritable_file_exception',
                                array('%path%' => $e->getPath()),
                                'platform'
                            )
                        )
                    );
                }
                $this->addFlashMessage('portal_configuration_updated_success');

                return $this->redirect($this->generateUrl('claro_admin_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    protected function addFlashMessage($message, $type = 'success')
    {
        $this->get('session')->getFlashBag()->add(
            $type,
            $this->get('translator')->trans($message, array(), 'platform')
        );
    }
}
