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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * This controller is used to do some redirections/route alias. It's not always possible to do it
 * in the concerned controller because path do have prefixes we want to remove/override sometimes.
 */
class RedirectController extends Controller
{
    /**
     * @EXT\Route("ws/{slug}/")
     * @EXT\Route("ws/{slug}")
     * @EXT\ParamConverter("workspace",  options={"mapping": {"slug": "slug"}})
     */
    public function openWorkspaceSlugAction(Workspace $workspace)
    {
        return $this->redirectToRoute('claro_workspace_open', ['workspaceId' => $workspace->getId()]);
    }
}
