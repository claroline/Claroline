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

use Claroline\CoreBundle\Entity\Competence\Competence;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Form\CompetenceType;
use Claroline\CoreBundle\Manager\CompetenceManager;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CompetenceController
{
    private $formFactory;
    private $competenceManager;
    private $request;
    private $om;
    private $adminTool;
    private $sc;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "competenceManager"  = @DI\Inject("claroline.manager.competence_manager"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "sc"                 = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        Request $request,
        RouterInterface $router,
        CompetenceManager $competenceManager,
        ObjectManager $om,
        ToolManager $toolManager,
        SecurityContextInterface $sc
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->competenceManager = $competenceManager;
        $this->om = $om;
        $this->adminTool = $toolManager->getAdminToolByName('competence_referencial');
        $this->sc = $sc;
    }

     /**
      * @EXT\Route(
      *     "/admin/learning/outcomes/list",
      *     name="claro_admin_learning_outcomes_list",
      *    options={"expose"=true}
      * )
      * @EXT\Template()
      *
      * Displays list of learning outcomes
      *
      * @return \Symfony\Component\HttpFoundation\Response
      */
    public function adminLearningOutcomesListAction()
    {
        $this->checkOpen();
        $learningOutcomes = $this->competenceManager->getRootCompetenceNodes();

     	return array('learningOutcomes' => $learningOutcomes);
    }

    /**
     * @EXT\Route(
     *     "/show/learning/outcomes/{competenceNode}",
     *     name="claro_show_admin_learning_outcomes",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * Show all the hiearchy from a competence
     *
     */
    public function showAdminLearningOutcomesAction(CompetenceNode $competenceNode)
    {
        $this->checkOpen();
        $competenceHierarchy = $this->competenceManager
            ->getHierarchyByCompetenceNode($competenceNode);

        return array(
            'competenceHierarchy' => $competenceHierarchy,
            'competenceNode' => $competenceNode
//            'tree' => $this->competenceManager->getHierarchy($competenceNode)
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/competences/management/ordered/by/{orderedBy}/order/{order}/page/{page}/max/{max}",
     *     name="claro_admin_competences_management",
     *     defaults={"ordered"="name","order"="ASC","page"=1,"max"=20}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:adminCompetencesManagement.html.twig")
     *
     * Show all admin competences
     *
     */
    public function adminCompetencesManagementAction(
        $orderedBy = 'name',
        $order = 'ASC',
        $page = 1,
        $max = 20
    )
    {
        $this->checkOpen();
        $competences = $this->competenceManager
            ->getAdminCompetences($orderedBy, $order, $page, $max);

        return array(
            'competences' => $competences,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'max' => $max
        );
    }

    /**
     * @EXT\Route(
     *     "admin/learning/outcomes/create/form",
     *     name="claro_admin_learning_outcomes_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     */
    public function adminLearningOutcomesCreateForm()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new CompetenceType());

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_admin_learning_outcomes_create',
                array()
            )
        );
    }

    /**
     * @EXT\Route(
     *     "admin/learning/outcomes/create",
     *     name="claro_admin_learning_outcomes_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAdminLearningOutcomesAction()
    {
        $this->checkOpen();
        $competence = new Competence();
        $form = $this->formFactory->create(new CompetenceType(), $competence);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence->setIsPlatform(true);
            $this->competenceManager->persistCompetence($competence);
            $node = $this->competenceManager->createCompetenceNode($competence);

            return new JsonResponse(
                array(
                    'id' => $node->getId(),
                    'competence' => array('name' => $node->getCompetence()->getName())
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_admin_learning_outcomes_create',
                array()
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/competence/{competence}/edit/form",
     *     name="claro_admin_competence_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param Competence $competence
     *
     * Edit a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminCompetenceEditFormAction(Competence $competence)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(
            new CompetenceType(),
            $competence
        );

        return array(
            'form' => $form->createView(),
            'competence' => $competence
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/competence/{competence}/edit",
     *     name="claro_admin_competence_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:adminCompetenceEditForm.html.twig")
     *
     * @param Competence $competence
     *
     * Edit a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminCompetenceEditAction(Competence $competence)
    {
        $this->checkOpen();
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
            'competence' => $competence
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/competence/create/form",
     *     name="claro_admin_competence_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * Creates a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminCompetenceCreateFormAction()
    {
        $this->checkOpen();
        $competence = new Competence();
        $form = $this->formFactory->create(
            new CompetenceType(),
            $competence
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/competence/create",
     *     name="claro_admin_competence_create",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:adminCompetenceCreateForm.html.twig")
     *
     * Creates a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminCompetenceCreateAction()
    {
        $this->checkOpen();
        $competence = new Competence();
        $form = $this->formFactory->create(
            new CompetenceType(),
            $competence
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence->setIsPlatform(true);
            $this->competenceManager->persistCompetence($competence);

            return new JsonResponse('success', 200);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/delete/admin/competence/{competence}",
     *     name="claro_admin_competence_delete",
     *     options={"expose"=true}
     * )
     * @param Competence $competence
     *
     * Delete a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAdminCompetenceAction(Competence $competence)
    {
        $this->checkOpen();
        $this->competenceManager->deleteCompetence($competence);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/competence/node/{parent}/sub/competence/create/form",
     *     name="claro_admin_sub_competence_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     *
     * @param  CompetenceNode $parent
     * @return
     */
    public function adminSubCompetenceCreateFormAction(CompetenceNode $parent)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new CompetenceType());

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_admin_sub_competence_create',
                array('parent' => $parent->getId())
            )
        );
    }
    /**
     * @EXT\Route(
     *     "/competence/node/{parent}/sub/competence/create",
     *     name="claro_admin_sub_competence_create",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     *
     * @param  CompetenceNode $parent
     * @return
     */
    public function adminSubCompetenceCreateAction(CompetenceNode $parent)
    {
        $this->checkOpen();
        $competence = new Competence();
        $form = $this->formFactory->create(new CompetenceType(), $competence);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence->setIsPlatform(true);
            $this->competenceManager->persistCompetence($competence);
            $this->competenceManager->createCompetenceNode($competence, $parent);

            return new JsonResponse('success', 200);
        }

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate(
                'claro_admin_sub_competence_create',
                array('parent' => $parent->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/delete/admin/competence/node/{competenceNode}",
     *     name="claro_admin_competence_node_delete",
     *     options={"expose"=true}
     * )
     * @param CompetenceNode $competenceNode
     *
     * Delete a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAdminCompetenceNodeAction(CompetenceNode $competenceNode)
    {
        $this->checkOpen();
        $this->competenceManager->deleteCompetenceNode($competenceNode);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/admin/competence/node/{parent}/sub/competence/link/form",
     *     name="claro_admin_sub_competence_link_form",
     *     options={"expose"=true}
     * )
     * @Ext\Template()
     */
    public function adminSubCompetenceLinkFormAction(CompetenceNode $parent)
    {
        $this->checkOpen();
        $linkableCompetences = $this->competenceManager
            ->getLinkableAdminCompetences($parent);

        return array(
            'linkableCompetences' => $linkableCompetences,
            'parent' => $parent
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/competence/node/{parent}/sub/competence/{competence}/link",
     *     name="claro_admin_sub_competence_link",
     *     options={"expose"=true}
     * )
     */
    public function adminSubCompetenceLinkAction(
        CompetenceNode $parent,
        Competence $competence
    )
    {
        $this->checkOpen();
        $this->competenceManager->createCompetenceNode($competence, $parent);
        $root = $this->competenceManager->getCompetenceNodeById($parent->getRoot());

        return new RedirectResponse(
            $this->router->generate(
                'claro_show_admin_learning_outcomes',
                array('competenceNode' => $root->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/competence/{competence}/view",
     *     name="claro_admin_competence_view",
     *     options={"expose"=true}
     * )
     * @Ext\Template()
     */
    public function adminCompetenceViewAction(Competence $competence)
    {
        $this->checkOpen();

        return array('competence' => $competence);
    }


    /**********************************************************************
     **********************************************************************
     **                                                                  **
     **     The code below has to be checked and removed if useless.     **
     **                                                                  **
     **********************************************************************
     **********************************************************************/

//
//    /**
//     * @EXT\Route("/form", name="claro_admin_competence_form", options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
//     */
//    public function addCompetenceModalForm()
//    {
//        $this->checkOpen();
//        $form = $this->formFactory->create(new CompetenceType());
//
//        return array(
//            'form' => $form->createView(),
//            'action' => $this->router->generate('claro_admin_competence_add', array())
//        );
//    }
//
//    /**
//     * @EXT\Route("/competence/{competence}/hierarchy/form", name="claro_admin_competence_hierarchy_form", options={"expose"=true})
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
//     *
//     * @param  Competence $competence
//     * @return
//     */
//    public function formCompetenceNodeAction(CompetenceNode $competence)
//    {
//        $this->checkOpen();
//        $form = $this->formFactory->create(new CompetenceType());
//
//        return array(
//            'form' => $form->createView(),
//            'action' => $this
//                ->router
//                ->generate(
//                    'claro_admin_competence_hierarchy_add',
//                    array('competence' => $competence->getId()
//                )
//            )
//        );
//    }
//
//    /**
//     * @EXT\Route("/competence/{competence}/hierarchy/add", name="claro_admin_competence_hierarchy_add", options={"expose"=true})
//     * @EXT\Method("POST")
//     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
//     */
//    public function addCompetenceNode(CompetenceNode $competence)
//    {
//        $form = $this->formFactory->create(new CompetenceType(), new Competence());
//        $form->handleRequest($this->request);
//
//        if ($form->isValid()) {
//            $subCpt = $form->getData();
//            $this->competenceManager->addSub($competence, $subCpt);
//
//            return new JsonResponse(array());
//        }
//
//        return array(
//            'form' => $form->createView(),
//            'action' => $this
//                ->router
//                ->generate(
//                    'claro_admin_competence_hierarchy_add',
//                    array('competence' => $competence->getId()
//                )
//            )
//        );
//    }
//
//
//    /**
//     * @EXT\Route("/addsubcpt/{competenceId}", name="claro_admin_competence_add_sub", options={"expose"=true})
//     * @EXT\Method({"GET","POST"})
//     * @EXT\ParamConverter(
//     *      "competence",
//     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
//     *      options={"id" = "competenceId", "strictId" = true}
//     * )
//     * @param Competence $competence
//     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceForm.html.twig")
//     *
//     * Add a sub competence
//     *
//     */
//    public function subCompetenceAction($competence)
//    {
//        $this->checkOpen();
//        $form = $this->formFactory->create(new CompetenceType());
//        $form->handleRequest($this->request);
//
//        if ($form->isValid()) {
//            $subCpt = $form->getData();
//            if($this->competenceManager->addSub($competence, $subCpt)) {
//            	    return new RedirectResponse(
//                    $this->router->generate('claro_admin_competences')
//                );
//            }
//         }
//
//        return array(
//        	'form' => $form->createView(),
//        	'cpt' => $competence,
//        	'route' => 'claro_admin_competence_add_sub'
//        );
//    }
//
//    /**
//     * @EXT\Route("
//     *     /modify/{competenceNode}",
//     *     name="claro_admin_competence_modify"
//     * )
//     * @EXT\Method({"GET","POST"})
//     * @param Competence $competence
//     * @EXT\Template()
//     *
//     * Add a sub competence
//     *
//     */
//    public function modifyCompetenceAction(CompetenceNode $competenceNode)
//    {
//        $form = $this->formFactory->create(
//            new CompetenceType(),
//            $competenceNode->getCompetence()
//        );
//        $addForm = $this->formFactory->create(new CompetenceType());
//        $form->handleRequest($this->request);
//
//        if ($form->isValid()) {
//         	$this->competenceManager->updateCompetence($competenceNode);
//        }
//
//        return array(
//            'form' => $form->createView(),
//            'competenceNode' => $competenceNode,
//            'route' => 'claro_admin_competence_modify',
//            'addForm' => $addForm->createView()
//        );
//    }
//
//
//    /**
//     * @EXT\Route(
//     *     "/move/{competenceNode}",
//     *     name="claro_admin_competence_move_form"
//     * )
//     * @EXT\Template()
//     * @param Competence $competence
//     *
//     * move a competence
//     *
//     */
//    public function competenceMoveFormAction(CompetenceNode $competenceNode)
//    {
//    	$competenceHierarchy = $this->competenceManager
//            ->getHierarchyNameNoHtml($competenceNode);
//        return array(
//        	'competenceNode' => $competenceNode,
//        	'competenceHierarchy' => $competenceHierarchy
//        );
//    }
//
//    /**
//     * @EXT\Route(
//     *     "/move/{parent}/add",
//     *     name="claro_admin_competence_move",
//     *     options={"expose"=true}
//     * )
//     * @EXT\Method("POST")
//     * @EXT\ParamConverter(
//     *     "competences",
//     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
//     *      options={"multipleIds" = true, "name" = "competences"}
//     * )
//     * @param Competence $competences
//     *
//     * move a competence
//     *
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function moveCompetenceAction(array $competences, CompetenceNode $parent)
//    {
//    	if ($this->competenceManager->move($competences,$parent)) {
//
//    	   return new Response(200);
//    	}
//    }

//
//     /**
//     * @EXT\Route("/get/{competenceId}", name="claro_admin_competence_full_hierarchy",options={"expose"=true})
//     * @EXT\Method("POST")
//     * @EXT\ParamConverter(
//     *      "competence",
//     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
//     *      options={"id" = "competenceId", "strictId" = true}
//     * )
//     * @param Competence $competences
//     *
//     * get the html structure of the hole Learning outcome
//     *
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function getFullCompetenceNodeAction($competence)
//    {
//    	$tree = $this->competenceManager->getHierarchy($competence);
//    	return new Response(
//        	json_encode(
//        		array(
//        			'tree' => $tree
//        		)
//        	),
//        	200,
//        	array('Content-Type' => 'application/json')
//        );
//    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->adminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
