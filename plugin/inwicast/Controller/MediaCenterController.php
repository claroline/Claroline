<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 2/19/15
 */

namespace Icap\InwicastBundle\Controller;

use Icap\InwicastBundle\Entity\MediaCenter;
use Icap\InwicastBundle\Exception\InvalidMediacenterFormException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/inwicast/mediacenter")
 * Class MediaCenterController
 */
class MediaCenterController extends Controller
{
    /**
     * @Route("/admin/configure", name="inwicast_mediacenter_configure")
     * @Method({"GET", "POST"})
     * @Template("IcapInwicastBundle:MediaCenter:form.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function configureAction(Request $request)
    {
        $this->checkAdmin();
        $mediacenterManager = $this->getMediacenterManager();
        $mediacenter = $mediacenterManager->getMediacenterOrEmpty();
        try {
            $mediacenter = $mediacenterManager->processForm($mediacenter, $request);
        } catch (InvalidMediacenterFormException $imfe) {
            return ['form' => $imfe->getForm()->createView()];
        }

        $response = $this->forward(
            'IcapInwicastBundle:MediaCenter:success',
            [
                'mediacenter' => $mediacenter,
            ]
        );

        return $response;
    }

    /**
     * @Route("/admin/configure/success", name="inwicast_mediacenter_configure_success")
     * @Method({"GET"})
     * @Template()
     *
     * @param MediaCenter $mediacenter
     *
     * @return array
     */
    public function successAction(MediaCenter $mediacenter = null)
    {
        $this->checkAdmin();
        if ($mediacenter === null) {
            $mediacenter = $this->getMediacenterManager()->getMediacenter();
        }

        return ['mediacenter' => $mediacenter];
    }
}
