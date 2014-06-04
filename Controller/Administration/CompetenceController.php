<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 */
class CompetenceController {

    /**
     * @DI\InjectParams({
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "groupManager"       = @DI\Inject("claroline.manager.group_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        GroupManager $groupManager,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        Request $request,
        RouterInterface $router
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->groupManager = $groupManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
    }

     /**
     * @EXT\Route("/new", name="claro_admin_competence_form")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:competenceForm.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function competenceFormAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);

        return array('form' => $form->createView());
    }
} 