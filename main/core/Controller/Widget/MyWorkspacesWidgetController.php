<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Widget;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyWorkspacesWidgetController extends Controller
{
    /**
     * @DI\InjectParams({
     * })
     */
    public function __construct(
    ) {
    }

    /**
     * @EXT\Route(
     *     "/workspaces/widget/{mode}",
     *     name="claro_display_workspaces_widget",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:widget:display_my_workspaces_widget.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Renders the workspaces list widget
     *
     * @return Response
     */
    public function displayMyWorkspacesWidgetAction($mode, User $user)
    {
        return ['workspaces' => [], 'mode' => $mode];
    }
}
