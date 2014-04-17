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

use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdministrationController extends Controller
{
    private $toolManager;
    private $sc;

    /**
     * @DI\InjectParams({
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "sc"    = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        SecurityContextInterface $sc
    )
    {
        $this->toolManager = $toolManager;
        $this->sc = $sc;
    }

    /**
     * @EXT\Route(
     *     "/index",
     *     name="claro_admin_index"
     * )
     *
     * Displays the administration section index.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $tools = $this->toolManager->getAdminToolsByRoles($this->sc->getToken()->getRoles());

        if (count($tools) === 0) {
            throw new AccessDeniedException();
        }

        return $this->redirect($this->generateUrl('claro_admin_open_tool', array('toolName' => $tools[0]->getName())));
    }
}