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

use Claroline\CoreBundle\Entity\Competence\Competence;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\CompetenceType;
use Claroline\CoreBundle\Manager\CompetenceManager;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CompetenceToolController extends Controller
{
    private $competenceManager;
    private $formFactory;
    private $learningOutcomesTool;
    private $request;
    private $router;
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "competenceManager"  = @DI\Inject("claroline.manager.competence_manager"),
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"         	= @DI\Inject("router"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "toolManager"	= @DI\Inject("claroline.manager.tool_manager")
     * 	})
     */
    public function __construct(
        CompetenceManager $competenceManager,
        FormFactoryInterface $formFactory,
        Request $request,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager
    )
    {
        $this->competenceManager = $competenceManager;
        $this->formFactory = $formFactory;
        $this->learningOutcomesTool =
            $toolManager->getOneToolByName('learning_outcomes');
        $this->request = $request;
        $this->router = $router;
        $this->securityContext = $securityContext;
    }

     /**
      * @EXT\Route(
      *     "/workspace/{workspace}/learning/outcomes/list",
      *     name="claro_workspace_learning_outcomes_list"
      * )
      * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceLearningOutcomesList.html.twig")
      *
      * Displays list of learning outcomes
      *
      * @return \Symfony\Component\HttpFoundation\Response
      */
    public function workspaceLearningOutcomesListAction(Workspace $workspace)
    {
        $this->checkOpen();
        $learningOutcomes = $this->competenceManager
            ->getRootCompetenceNodes($workspace);

        return array(
            'workspace' => $workspace,
            'learningOutcomes' => $learningOutcomes
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competences/management/ordered/by/{orderedBy}/order/{order}/page/{page}/max/{max}",
     *     name="claro_workspace_competences_management",
     *     defaults={"ordered"="name","order"="ASC","page"=1,"max"=20}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceCompetencesManagement.html.twig")
     *
     * Show all workspace competences
     *
     */
    public function workspaceCompetencesManagementAction(
        Workspace $workspace,
        $orderedBy = 'name',
        $order = 'ASC',
        $page = 1,
        $max = 20
    )
    {
        $this->checkOpen();
        $competences = $this->competenceManager
            ->getWorkspaceCompetences($workspace, $orderedBy, $order, $page, $max);

        return array(
            'workspace' => $workspace,
            'competences' => $competences,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'max' => $max
        );
    }
    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/create/form",
     *     name="claro_workspace_competence_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceCompetenceCreateForm.html.twig")
     *
     * Creates a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceCompetenceCreateFormAction(Workspace $workspace)
    {
        $this->checkOpen();
        $competence = new Competence();
        $form = $this->formFactory->create(
            new CompetenceType(),
            $competence
        );

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/create",
     *     name="claro_workspace_competence_create",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceCompetenceCreateForm.html.twig")
     *
     * Creates a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceCompetenceCreateAction(Workspace $workspace)
    {
        $this->checkOpen();
        $competence = new Competence();
        $form = $this->formFactory->create(
            new CompetenceType(),
            $competence
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence->setWorkspace($workspace);
            $competence->setIsPlatform(false);
            $this->competenceManager->persistCompetence($competence);

            return new JsonResponse('success', 200);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/{competence}/edit/form",
     *     name="claro_workspace_competence_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceCompetenceEditForm.html.twig")
     *
     * @param Competence $competence
     *
     * Edit a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceCompetenceEditFormAction(
        Workspace $workspace,
        Competence $competence
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $competence);
        $form = $this->formFactory->create(
            new CompetenceType(),
            $competence
        );

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'competence' => $competence
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/{competence}/edit",
     *     name="claro_workspace_competence_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceCompetenceEditForm.html.twig")
     *
     * @param Competence $competence
     *
     * Edit a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceCompetenceEditAction(
        Workspace $workspace,
        Competence $competence
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $competence);
        $form = $this->formFactory->create(
            new CompetenceType(),
            $competence
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->competenceManager->persistCompetence($competence);

            return new JsonResponse('success', 200);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'competence' => $competence
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/{competence}/delete",
     *     name="claro_workspace_competence_delete",
     *     options={"expose"=true}
     * )
     * @param Competence $competence
     *
     * Delete a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceCompetenceDeleteAction(
        Workspace $workspace,
        Competence $competence
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $competence);
        $this->competenceManager->deleteCompetence($competence);

        return new JsonResponse('success', 200);
    }


    /**
     * @EXT\Route(
     *     "workpace/{workspace}/learning/outcomes/create/form",
     *     name="claro_workspace_learning_outcomes_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceLearningOutcomesCreateForm.html.twig")
     */
    public function workspaceLearningOutcomesCreateForm(Workspace $workspace)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new CompetenceType());

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/workpace/{workspace}/learning/outcomes/create",
     *     name="claro_workspace_learning_outcomes_create",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceLearningOutcomesCreateForm.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceLearningOutcomesCreateAction(Workspace $workspace)
    {
        $this->checkOpen();
        $competence = new Competence();
        $form = $this->formFactory->create(new CompetenceType(), $competence);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence->setWorkspace($workspace);
            $competence->setIsPlatform(false);
            $this->competenceManager->persistCompetence($competence);
            $this->competenceManager->createCompetenceNode($competence);

            return new JsonResponse('success', 200);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }


    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/node/{competenceNode}/delete",
     *     name="claro_workspace_competence_node_delete",
     *     options={"expose"=true}
     * )
     * @param CompetenceNode $competenceNode
     *
     * Delete a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteWorkspaceCompetenceNodeAction(
        Workspace $workspace,
        CompetenceNode $competenceNode
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $competenceNode->getCompetence());
        $this->competenceManager->deleteCompetenceNode($competenceNode);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/learning/outcomes/{competenceNode}/show",
     *     name="claro_workspace_learning_outcomes_show"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceLearningOutcomesShow.html.twig")
     *
     * Show all the hiearchy from a learning outcomes
     *
     */
    public function workspaceLearningOutcomesShowAction(
        Workspace $workspace,
        CompetenceNode $competenceNode
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $competenceNode->getCompetence());
        $competenceHierarchy = $this->competenceManager
            ->getHierarchyByCompetenceNode($competenceNode);

        return array(
            'workspace' => $workspace,
            'competenceHierarchy' => $competenceHierarchy,
            'competenceNode' => $competenceNode
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/node/{parent}/sub/competence/create/form",
     *     name="claro_workspace_sub_competence_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceSubCompetenceCreateForm.html.twig")
     *
     * @param  Workspace $workspace
     * @param  CompetenceNode $parent
     */
    public function workspaceSubCompetenceCreateFormAction(
        Workspace $workspace,
        CompetenceNode $parent
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $parent->getCompetence());
        $form = $this->formFactory->create(new CompetenceType());

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'parent' => $parent
        );
    }
    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/node/{parent}/sub/competence/create",
     *     name="claro_workspace_sub_competence_create",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceSubCompetenceCreateForm.html.twig")
     *
     * @param  Workspace $workspace
     * @param  CompetenceNode $parent
     */
    public function workspaceSubCompetenceCreateAction(
        Workspace $workspace,
        CompetenceNode $parent
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $parent->getCompetence());
        $competence = new Competence();
        $form = $this->formFactory->create(new CompetenceType(), $competence);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence->setWorkspace($workspace);
            $competence->setIsPlatform(false);
            $this->competenceManager->persistCompetence($competence);
            $this->competenceManager->createCompetenceNode($competence, $parent);

            return new JsonResponse('success', 200);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'parent' => $parent
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/node/{parent}/sub/competence/link/form",
     *     name="claro_workspace_sub_competence_link_form",
     *     options={"expose"=true}
     * )
     * @Ext\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceSubCompetenceLinkForm.html.twig")
     */
    public function workspaceSubCompetenceLinkFormAction(
        Workspace $workspace,
        CompetenceNode $parent
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $parent->getCompetence());
        $linkableCompetences = $this->competenceManager
            ->getLinkableWorkspaceCompetences($workspace, $parent);

        return array(
            'workspace' => $workspace,
            'linkableCompetences' => $linkableCompetences,
            'parent' => $parent
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/node/{parent}/sub/competence/{competence}/link",
     *     name="claro_workspace_sub_competence_link",
     *     options={"expose"=true}
     * )
     */
    public function workspaceSubCompetenceLinkAction(
        Workspace $workspace,
        CompetenceNode $parent,
        Competence $competence
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $parent->getCompetence());
        $this->checkCompetenceAccess($workspace, $competence);
        $this->competenceManager->createCompetenceNode($competence, $parent);
        $root = $this->competenceManager->getCompetenceNodeById($parent->getRoot());

        return new RedirectResponse(
            $this->router->generate(
                'claro_workspace_learning_outcomes_show',
                array(
                    'workspace' => $workspace->getId(),
                    'competenceNode' => $root->getId()
                )
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/competence/{competence}/view",
     *     name="claro_workspace_competence_view",
     *     options={"expose"=true}
     * )
     * @Ext\Template("ClarolineCoreBundle:Tool\workspace\competence:workspaceCompetenceView.html.twig")
     */
    public function workspaceCompetenceViewAction(
        Workspace $workspace,
        Competence $competence
    )
    {
        $this->checkOpen();
        $this->checkCompetenceAccess($workspace, $competence);

        return array('competence' => $competence);
    }

//    /**
//     * @EXT\Route("/{workspace}/menutab", name="claro_workspace_menu_tab")
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:menuTab.html.twig")
//     */
//    public function menuTabAction(Workspace $workspace)
//    {
//        return array('workspace' => $workspace);
//    }
//
//     /**
//     * @EXT\Route("/{workspace}/show", name="claro_workspace_competences", options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:competences.html.twig")
//     *
//     * Displays the competences root.
//     *
//     */
//    public function competenceShowAction(Workspace $workspace)
//    {
//    	$this->checkOpen();
//    	$competences = $this->cptmanager->getTransversalCompetences($workspace);
//    	$form = $this->formFactory->create(new CompetenceType());
//
//    	return array(
//    		'cpt' => $competences,
//    		'form' => $form->createView(),
//    		'workspace' => $workspace
//    	);
//    }
//
//    /**
//     * @EXT\Route("/form/{workspace}", name="claro_workspace_competence_form", options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
//     */
//    public function addCompetenceModalForm(Workspace $workspace)
//    {
//        $this->checkUserIsAllowed($this->rm->getManagerRole($workspace), $workspace);
//        $form = $this->formFactory->create(new CompetenceType());
//
//        return array(
//            'form' => $form->createView(),
//            'action' => $this->router->generate('claro_workspace_competence_add', array('workspace' => $workspace->getId()))
//        );
//    }
//
//     /**
//     * @EXT\Route("/add/{workspace}", name="claro_workspace_competence_add")
//     * @EXT\Method("POST")
//     * Add a learning Outcome.
//     *
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function addCompetenceAction(Workspace $workspace)
//    {
//        $form = $this->formFactory->create(new CompetenceType());
//        $form->handleRequest($this->request);
//
//        if ($form->isValid()) {
//            $competence = $form->getData();
//            if($competence = $this->cptmanager->add($competence, $workspace)) {
//            	return new JsonResponse(
//            		array(
//            			'id' => $competence->getId(),
//            			'name' => $competence->getCompetence()->getName()
//            		)
//                );
//            }
//        }
//        return array(
//        	'form' => $form->createView(),
//        	'route' => 'claro_admin_competence_add'
//        );
//    }
//
//    /**
//     * @EXT\Route("/list/{workspace}",
//     * name="claro_workspace_competence_users",options={"expose"=true})
//     * @EXT\Method("GET")
//     *
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:myCompetences.html.twig")
//     **/
//    public function listMyCompetencesAction(Workspace $workspace)
//    {
//    	$this->checkOpen();
//    	$user = $this->sc->getToken()->getUser();
//    	$listCompetence = $this->cptmanager->getUserCompetenceByWorkspace($workspace, $user);
//    	$form = $this->formFactory->create(new CompetenceType());
//    	return array(
//    		'list' => $listCompetence,
//    		'workspace' => $workspace,
//    		'form' => $form->createView()
//    	);
//    }
//
//    /**
//     * @EXT\Route("/{workspace}/show/referential/{competence}", name="claro_workspace_competence_show_referential",options={"expose"=true})
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:competenceReferential.html.twig")
//     *
//     * Show all the hiearchy from a competence
//     *
//     */
//    public function competenceReferentialAction(Workspace $workspace, CompetenceNode $competence)
//    {
//        $this->checkOpen();
//        $competences = $this->cptmanager->getHierarchyByCompetenceNode($competence);
//
//        return array(
//            'competences' => $competences,
//            'cpt' => $competence,
//            'tree' => $this->cptmanager->getHierarchy($competence),
//            'workspace' => $workspace
//        );
//    }
//
//    /**
//     * @EXT\Route("/{workspace}/competence/{competence}/hierarchy/form", name="claro_workspace_competence_hierarchy_form", options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
//     *
//     * @param  Competence $competence
//     * @return
//     */
//    public function formCompetenceNodeAction(Workspace $workspace, CompetenceNode $competence)
//    {
//        $this->checkOpen();
//        $form = $this->formFactory->create(new CompetenceType());
//
//        return array(
//            'form' => $form->createView(),
//            'action' => $this
//                ->router
//                ->generate(
//                    'claro_workspace_competence_hierarchy_add',
//                    array(
//                        'competence' => $competence->getId(),
//                        'workspace' => $workspace->getId()
//                )
//            )
//        );
//    }
//
//    /**
//     * @EXT\Route("/{workspace}/competence/{competence}/hierarchy/add", name="claro_workspace_competence_hierarchy_add", options={"expose"=true})
//     * @EXT\Method("POST")
//     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
//     */
//    public function addCompetenceNode(Workspace $workspace, CompetenceNode $competence)
//    {
//        $form = $this->formFactory->create(new CompetenceType());
//        $form->handleRequest($this->request);
//
//        if ($form->isValid()) {
//            $subCpt = $form->getData();
//            $this->cptmanager->addSub($competence, $subCpt);
//            $users = $this->userManager->getUsersByWorkspaces(array($workspace),1,20,false);
//            $this->cptmanager->subscribeUserToCompetences($users,array($subCpt));
//            return new JsonResponse(array());
//        }
//
//        return array(
//            'form' => $form->createView(),
//            'action' => $this
//                ->router
//                ->generate(
//                    'claro_workspace_competence_hierarchy_add',
//                    array('competence' => $competence->getId()
//                )
//            )
//        );
//    }
//
//    /**
//     * @EXT\Route("/{workspaceId}/management/",
//     *  name="claro_workspace_competences_subscription_lists",
//     *	defaults={"search"=""}, options = {"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\ParamConverter(
//     *      "workspace",
//     *      class="ClarolineCoreBundle:Workspace\Workspace",
//     *      options={"id" = "workspaceId", "strictId" = true}
//     * )
//     *
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:listSubscription.html.twig")
//     *
//     */
//    public function listSubscriptionAction($workspace)
//    {
//    	$search = '';
//    	$competences = $this->cptmanager->getTransversalCompetences();
//    	return array(
//			'cpt' => $competences,
//			'search' => $search,
//			'workspace' => $workspace
//    	);
//    }
//
//    /**
//     * @EXT\Route("/{workspace}/subscription/users/competences/show/{competenceId}",
//     *  name="claro_workspace_competences_subscription_details")
//     * @EXT\Method("GET")
//     * @EXT\ParamConverter(
//     *      "competence",
//     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
//     *      options={"id" = "competenceId", "strictId" = true}
//     * )
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:showSubscription.html.twig")
//     * @param Competence $competence
//     */
//
//    public function showSubscriptionAction($competence, $workspace)
//    {
//        $this->checkOpen();
//        $listUsersCompetences =
//        $this->cptmanager->getCompetencesAssociateUsers($competence);
//        $tree =  $this->cptmanager->getHierarchy($competence);
//
//        return array(
//            'listUsers' => $listUsersCompetences,
//            'cpt' => $competence,
//            'tree' => $tree,
//            'workspace' => $workspace
//        );
//    }
//
//    /**
//     * @EXT\Route("/{workspaceId}/subscription/",
//     * name="claro_workspace_competence_subcription_users_form",options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\ParamConverter(
//     *     "competences",
//     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
//     *      options={"multipleIds" = true, "name" = "competences"}
//     * )
//     * @EXT\ParamConverter(
//     *      "workspace",
//     *      class="ClarolineCoreBundle:Workspace\Workspace",
//     *      options={"id" = "workspaceId", "strictId" = true}
//     * )
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:subscription.html.twig")
//     **/
//    public function subscriptionAction(array $competences, $workspace)
//    {
//        $users = array();
//        foreach ($competences as $c) {
//            $users = array_merge($users,$this->cptmanager->getUserByCompetenceRoot($c));
//        }
//
//        if (count($users))
//        {
//    	   $pager = $this->userManager->getAllUsersExcept(1, 20, 'id', null, $users);
//        } else {
//            $pager = $this->userManager->getAllUsers(1);
//        }
//
//    	return array(
//    		'competences' => $competences,
//    		'users' => $pager,
//    		'search' => '',
//    		'workspace' => $workspace
//    	);
//    }
//
//    /**
//     * @EXT\Route("/{workspaceId}/subscription/users",
//     * name="claro_workspace_competence_subcription_users",options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\ParamConverter(
//     *     "competences",
//     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
//     *      options={"multipleIds" = true, "name" = "competences"}
//     * )
//     * @EXT\ParamConverter(
//     *     "users",
//     *      class="ClarolineCoreBundle:User",
//     *      options={"multipleIds" = true, "name" = "subjectIds"}
//     * )
//     *
//     * @EXT\ParamConverter(
//     *      "workspace",
//     *      class="ClarolineCoreBundle:Workspace\Workspace",
//     *      options={"id" = "workspaceId", "strictId" = true}
//     * )
//     **/
//    public function subscriptionUsersAction($workspace,array $users, array $competences)
//    {
//    	$this->cptmanager->subscribeUserToCompetences($users, $competences, $workspace);
//    	return New Response(200);
//    }
//
//    /**
//     * @EXT\Route("/{workspaceId}/subscription/users/competences",
//     * name="claro_workspace_competences_list_users",options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\ParamConverter(
//     *      "workspace",
//     *      class="ClarolineCoreBundle:Workspace\Workspace",
//     *      options={"id" = "workspaceId", "strictId" = true}
//     * )
//     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\competence:listUsers.html.twig")
//     **/
//    public function listUsersAction($workspace)
//    {
//    	$this->checkOpen();
//    	$listUsersCompetences =
//    	$this->cptmanager->getCompetencesAssociateUsers();
//
//    	return array(
//    		'listUsers' => $listUsersCompetences,
//    		'workspace' => $workspace
//    	);
//    }

    private function checkOpen()
    {
        if ($this->securityContext->isGranted('OPEN', $this->learningOutcomesTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }

    private function checkCompetenceAccess(
        Workspace $workspace,
        Competence $competence
    )
    {
        if ($workspace->getId() !== $competence->getWorkspace()->getId()) {

            throw new AccessDeniedException();
        }
    }

//    private function checkUserIsAllowed($permission, Workspace $workspace)
//    {
//        if (!$this->securityContext->isGranted($permission, $workspace)) {
//            throw new AccessDeniedException();
//        }
//    }
}