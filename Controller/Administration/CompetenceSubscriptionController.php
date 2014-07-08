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
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\CompetenceManager;
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
class CompetenceSubscriptionController {

	private $formFactory;
    private $cptmanager;
    private $request;
    private $userManager;
    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "cptmanager"			= @DI\Inject("claroline.manager.competence_manager"),
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        RouterInterface $router,
        CompetenceManager $cptmanager,
        UserManager $userManager
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->cptmanager = $cptmanager;
        $this->userManager = $userManager;
    }

    /**
     * @EXT\Route("/menu/",
     *  name="claro_admin_competences_subscription_menu")
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     */
    public function menuAction()
    {
    	return array();
    }

    /**
     * @EXT\Route("/management/",
     *  name="claro_admin_competences_subscription_lists", 		
     *	defaults={"search"=""}, options = {"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     */
    public function listSubscriptionAction()
    {
    	$search = '';
    	$competences = $this->cptmanager->getTransversalCompetences();
    	return array(
			'cpt' => $competences,
			'search' => $search
    	);
    }

    /**
     * @EXT\Route("/subscription/",  
     * name="claro_admin_competence_subcription_users_form",options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"multipleIds" = true, "name" = "competences"}
     * )
     * 
     * @EXT\Template()
     **/ 
    public function subscriptionAction(array $competences)
    {
    	$pager = $this->userManager->getAllUsers(1);
    	return array(
    		'competences' => $competences,
    		'users' => $pager,
    		'search' => ''
    	);
    }

    /**
     * @EXT\Route("/subscription/users",  
     * name="claro_admin_competence_subcription_users",options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"multipleIds" = true, "name" = "competences"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     * @EXT\Template()
     **/ 
    public function subscriptionUsersAction(array $users, array $competences)
    {
    	$this->cptmanager->subscribeUserToCompetences($users, $competences);
    	return New Response(200);
    }

    public function unsubscriptionUsersAction(array $users, array $competences)
    {
    	$this->cptmanager->unsubscribeUserToCompetences($users, $competences);
    	return new Response(200);
    }

    /**
     * @EXT\Route("/subscription/users/competences",  
     * name="claro_admin_competences_list_users")
     * @EXT\Method("GET")
     * @EXT\Template()
     **/ 
    public function listUsersAction()
    {
    	$listUsersCompetences =
    	$this->cptmanager->getCompetencesAssociateUsers();

    	return array( 'listUsers' => $listUsersCompetences );
    }
}