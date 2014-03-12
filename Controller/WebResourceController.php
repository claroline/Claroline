<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class WebResourceController extends Controller
{
    /**
     * @Route("/web/resource/{node}", name="claro_web_resource")
     */
    public function fileAction(ResourceNode $node)
    {
        $resource = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);

        return new Responce(
            $this->container->getParameter('claroline.param.files_directory')
            .DIRECTORY_SEPARATOR.$resource->getHashName()
        );
    }
}
