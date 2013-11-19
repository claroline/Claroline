<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ResourceTypeController extends Controller
{
    /**
     * @Template("ClarolineCoreBundle:Resource:configResourcesManager.html.twig")
     */
    public function initPickerAction($includeDependencies = true)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();

        return array('resourceTypes' => $resourceTypes, 'includeDependencies' => $includeDependencies);
    }
}