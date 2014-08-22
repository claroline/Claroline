<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Manager\userManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Manager\CompetenceManager;
use Claroline\CoreBundle\Manager\toolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\CompetenceType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CompetenceToolController extends Controller
{
	private $formFactory;
	private $request;
	private $router;
	private $cptmanager;
	private $sc;
	private $userManager;
	private $toolManager;
    private $rm;
	/**
	 * @DI\InjectParams({
	 * "securityContext"    = @DI\Inject("security.context"),
	 * "formFactory"        = @DI\Inject("form.factory"),
     * "request"            = @DI\Inject("request"),
     * "router"         	= @DI\Inject("router"),
     * "cptmanager"			= @DI\Inject("claroline.manager.competence_manager"),
     * "userManager"		= @DI\Inject("claroline.manager.user_manager"),
     * "toolManager"		= @DI\Inject("claroline.manager.tool_manager"),
     * "rm"                 = @DI\Inject("claroline.manager.role_manager")
	 * 	})
	 */
	
	public function __construct(
        FormFactoryInterface $formFactory,
        Request $request,
        RouterInterface $router,
        CompetenceManager $cptmanager,
        SecurityContextInterface $securityContext,
        userManager $userManager,
        toolManager $toolManager,
        RoleManager $rm
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->cptmanager = $cptmanager;
        $this->sc = $securityContext;
        $this->userManager = $userManager;
        $this->toolManager = $toolManager->getOneToolByName('learning_profil');
        $this->rm = $rm;
    }

    /**
     * @EXT\Route("/{workspace}/menutab", name="claro_workspace_menu_tab")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:menuTab.html.twig")
     */
    public function menuTabAction(Workspace $workspace)
    {
        return array('workspace' => $workspace);
    }

     /**
     * @EXT\Route("/{workspace}/show", name="claro_workspace_competences", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:competences.html.twig")
     *
     * Displays the competences root.
     *
     */
    public function competenceShowAction(Workspace $workspace)
    {
    	$this->checkOpen();
    	$competences = $this->cptmanager->getTransversalCompetences($workspace);
    	$form = $this->formFactory->create(new CompetenceType());

    	return array(
    		'cpt' => $competences,
    		'form' => $form->createView(),
    		'workspace' => $workspace
    	);
    }

    /**
     * @EXT\Route("/form/{workspace}", name="claro_workspace_competence_form", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     */
    public function addCompetenceModalForm(Workspace $workspace)
    {
        $this->checkUserIsAllowed($this->rm->getManagerRole($workspace), $workspace);
        $form = $this->formFactory->create(new CompetenceType());

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate('claro_workspace_competence_add', array('workspace' => $workspace->getId()))
        );
    }

     /**
     * @EXT\Route("/add/{workspace}", name="claro_workspace_competence_add")
     * @EXT\Method("POST")
     * Add a learning Outcome.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCompetenceAction(Workspace $workspace)
    {
        $form = $this->formFactory->create(new CompetenceType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence = $form->getData();
            if($competence = $this->cptmanager->add($competence, $workspace)) {
            	return new JsonResponse(
            		array(
            			'id' => $competence->getId(),
            			'name' => $competence->getCompetence()->getName()
            		)
                );
            } 
        } 
        return array(
        	'form' => $form->createView(),
        	'route' => 'claro_admin_competence_add'
        );
    }

    /**
     * @EXT\Route("/list/{workspace}",  
     * name="claro_workspace_competence_users",options={"expose"=true})
     * @EXT\Method("GET")
     * 
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:myCompetences.html.twig")
     **/ 
    public function listMyCompetencesAction(Workspace $workspace)
    {
    	$this->checkOpen();
    	$user = $this->sc->getToken()->getUser();
    	$listCompetence = $this->cptmanager->getUserCompetenceByWorkspace($workspace, $user);
    	$form = $this->formFactory->create(new CompetenceType());
    	return array(
    		'list' => $listCompetence,
    		'workspace' => $workspace,
    		'form' => $form->createView()
    	);
    }

    /**
     * @EXT\Route("/{workspace}/show/referential/{competence}", name="claro_workspace_competence_show_referential",options={"expose"=true})
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:competenceReferential.html.twig")
     *
     * Show all the hiearchy from a competence
     *
     */
    public function competenceReferentialAction(Workspace $workspace, CompetenceNode $competence)
    {
        $this->checkOpen();
        $competences = $this->cptmanager->getHierarchyName($competence);
           
        return array(
            'competences' => $competences,
            'cpt' => $competence,
            'tree' => $this->cptmanager->getHierarchy($competence),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route("/{workspace}/competence/{competence}/hierarchy/form", name="claro_workspace_competence_hierarchy_form", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     * 
     * @param  Competence $competence 
     * @return              
     */
    public function formCompetenceNodeAction(Workspace $workspace, CompetenceNode $competence)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new CompetenceType());

        return array(
            'form' => $form->createView(),
            'action' => $this
                ->router
                ->generate(
                    'claro_workspace_competence_hierarchy_add', 
                    array(
                        'competence' => $competence->getId(),
                        'workspace' => $workspace->getId()
                )
            )
        );
    }

    /**
     * @EXT\Route("/{workspace}/competence/{competence}/hierarchy/add", name="claro_workspace_competence_hierarchy_add", options={"expose"=true})
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     */
    public function addCompetenceNode(Workspace $workspace, CompetenceNode $competence)
    {
        $form = $this->formFactory->create(new CompetenceType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {            
            $subCpt = $form->getData();
            $this->cptmanager->addSub($competence, $subCpt);
            $users = $this->userManager->getUsersByWorkspaces(array($workspace),1,20,false);
            $this->cptmanager->subscribeUserToCompetences($users,array($subCpt));
            return new JsonResponse(array());
        }  

        return array(
            'form' => $form->createView(),
            'action' => $this
                ->router
                ->generate(
                    'claro_workspace_competence_hierarchy_add', 
                    array('competence' => $competence->getId()
                )
            )
        );
    }

    /**
     * @EXT\Route("/{workspaceId}/management/",
     *  name="claro_workspace_competences_subscription_lists", 		
     *	defaults={"search"=""}, options = {"expose"=true})
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * 
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:listSubscription.html.twig")
     *
     */
    public function listSubscriptionAction($workspace)
    {
    	$search = '';
    	$competences = $this->cptmanager->getTransversalCompetences();
    	return array(
			'cpt' => $competences,
			'search' => $search,
			'workspace' => $workspace
    	);
    }

    /**
     * @EXT\Route("/{workspace}/subscription/users/competences/show/{competenceId}",
     *  name="claro_workspace_competences_subscription_details")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:showSubscription.html.twig")
     * @param Competence $competence
     */
    
    public function showSubscriptionAction($competence, $workspace)
    {
        $this->checkOpen();
        $listUsersCompetences =
        $this->cptmanager->getCompetencesAssociateUsers($competence);
        $tree =  $this->cptmanager->getHierarchy($competence);

        return array(
            'listUsers' => $listUsersCompetences,
            'cpt' => $competence,
            'tree' => $tree,
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route("/{workspaceId}/subscription/",  
     * name="claro_workspace_competence_subcription_users_form",options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"multipleIds" = true, "name" = "competences"}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:subscription.html.twig")
     **/ 
    public function subscriptionAction(array $competences, $workspace)
    {
        $users = array();
        foreach ($competences as $c) {
            $users = array_merge($users,$this->cptmanager->getUserByCompetenceRoot($c));
        }
        
        if (count($users)) 
        {    
    	   $pager = $this->userManager->getAllUsersExcept(1, 20, 'id', null, $users);
        } else {
            $pager = $this->userManager->getAllUsers(1);
        }

    	return array(
    		'competences' => $competences,
    		'users' => $pager,
    		'search' => '',
    		'workspace' => $workspace
    	);
    }

    /**
     * @EXT\Route("/{workspaceId}/subscription/users",  
     * name="claro_workspace_competence_subcription_users",options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"multipleIds" = true, "name" = "competences"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     **/ 
    public function subscriptionUsersAction($workspace,array $users, array $competences)
    {
    	$this->cptmanager->subscribeUserToCompetences($users, $competences, $workspace);
    	return New Response(200);
    }

    /**
     * @EXT\Route("/{workspaceId}/subscription/users/competences",  
     * name="claro_workspace_competences_list_users",options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:listUsers.html.twig")
     **/ 
    public function listUsersAction($workspace)
    {
    	$this->checkOpen();
    	$listUsersCompetences =
    	$this->cptmanager->getCompetencesAssociateUsers();

    	return array(
    		'listUsers' => $listUsersCompetences,
    		'workspace' => $workspace
    	);
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->toolManager)) {
            return true;
        }

        throw new AccessDeniedException();
    }

    private function checkUserIsAllowed($permission, Workspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }

}