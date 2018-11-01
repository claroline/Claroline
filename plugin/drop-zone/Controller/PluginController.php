<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Controller;

use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @EXT\Route("/dropzone", options={"expose"=true})
 */
class PluginController extends Controller
{
    /**
     * @SEC\PreAuthorize("canOpenAdminTool('main_settings')")
     * @EXT\Route("/plugin/configure", name="claro_dropzone_plugin_configure")
     * @EXT\Template()
     */
    public function configureAction()
    {
        return [];
    }
}
