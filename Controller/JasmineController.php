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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class JasmineController extends Controller
{
    /**
     * @Route(
     *     "/jasmine",
     *     name="claro_jasmine_spec_runner"
     * )
     *
     * @Template("ClarolineCoreBundle:Jasmine:specRunner.html.twig")
     */
    public function indexAction()
    {
        return array();
    }
}
