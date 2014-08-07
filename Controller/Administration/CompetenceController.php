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

use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Competence\Competence;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Manager\CompetenceManager;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CompetenceController {

    private $formFactory;
    private $cptmanager;
    private $request;
    private $om;
    private $toolManager;
    private $sc;
    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "cptmanager"			= @DI\Inject("claroline.manager.competence_manager"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "sc"                 = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        RouterInterface $router,
        CompetenceManager $cptmanager,
        ObjectManager $om,
        ToolManager $toolManager,
        SecurityContextInterface $sc
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->cptmanager = $cptmanager;
        $this->om = $om;
        $this->toolManager = $toolManager->getAdminToolByName('competence_referencial');
        $this->sc = $sc;
    }

     /**
     * @EXT\Route("/show", name="claro_admin_competences", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competences.html.twig")
     *
     * Displays the competences root.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function competencesAction()
    {
        $this->checkOpen();
     	return array('cpt' => $this->cptmanager->getTransversalCompetences());
    }

    /**
     * @EXT\Route("/form", name="claro_admin_competence_form", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     */
    public function addCompetenceModalForm()
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate('claro_admin_competence_add', array())
        );
    }

    /**
     * @EXT\Route("/show/referential/{competence}", name="claro_admin_competence_show_referential",options={"expose"=true})
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceReferential.html.twig")
     *
     * Show all the hiearchy from a competence
     *
     */
    public function competenceReferentialAction(CompetenceNode $competence)
    {
        $this->checkOpen();
        $competences = $this->cptmanager->getHierarchyName($competence);
           
        return array(
        	'competences' => $competences,
            'cpt' => $competence,
            'tree' => $this->cptmanager->getHierarchy($competence)
        );
    }

    /**
     * @EXT\Route("/competence/{competence}/hierarchy/form", name="claro_admin_competence_hierarchy_form", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     * 
     * @param  Competence $competence 
     * @return              
     */
    public function formCompetenceNodeAction(CompetenceNode $competence)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);

        return array(
            'form' => $form->createView(),
            'action' => $this
                ->router
                ->generate(
                    'claro_admin_competence_hierarchy_add', 
                    array('competence' => $competence->getId()
                )
            )
        );
    }

    /**
     * @EXT\Route("/competence/{competence}/hierarchy/add", name="claro_admin_competence_hierarchy_add", options={"expose"=true})
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     */
    public function addCompetenceNode(CompetenceNode $competence)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {            
            $subCpt = $form->getData();
            $this->cptmanager->addSub($competence, $subCpt);

            return new JsonResponse(array());
        }  

        return array(
            'form' => $form->createView(),
            'action' => $this
                ->router
                ->generate(
                    'claro_admin_competence_hierarchy_add', 
                    array('competence' => $competence->getId()
                )
            )
        );
    }

     /**
     * @EXT\Route("/add", name="claro_admin_competence_add")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceModalForm.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCompetenceAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence = $form->getData();
            $competence = $this->cptmanager->add($competence);

            return new JsonResponse(array(
                    'id' => $competence->getId(),
                    'name' => $competence->getCompetence()->getName()
                )
            );
        } 

        return array(
            'form' => $form->createView(),
            'action' => $this->router->generate('claro_admin_competence_add', array())
        );
    }

    /**
     * @EXT\Route("/addsubcpt/{competenceId}", name="claro_admin_competence_add_sub", options={"expose"=true})
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @param Competence $competence
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceForm.html.twig")
     *
     * Add a sub competence
     *
     */
    public function subCompetenceAction($competence)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {        	
            $subCpt = $form->getData();
            if($this->cptmanager->addSub($competence, $subCpt)) {
            	    return new RedirectResponse(
                    $this->router->generate('claro_admin_competences')
                );
            } 
         }  
           
        return array(
        	'form' => $form->createView(),
        	'cpt' => $competence,
        	'route' => 'claro_admin_competence_add_sub'
        );
    }

    /**
     * @EXT\Route("/modify/{competenceId}", name="claro_admin_competence_modify")
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @param Competence $competence
     * @EXT\Template()
     *
     * Add a sub competence
     *
     */
    public function modifyCompetenceAction($competence)
    {
	 	$form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE, array(), $competence->getCompetence());
        $addForm = $this->formFactory->create(FormFactory::TYPE_COMPETENCE, array());
        $form->handleRequest($this->request);
        if ($form->isValid()) {
         	$this->cptmanager->updateCompetence($competence);
        }

        return array(
        	'form' => $form->createView(),
        	'cpt' => $competence,
        	'route' => 'claro_admin_competence_modify',
            'addForm' => $addForm->createView()
        );
    }

    /**
     * @EXT\Route("/delete/{competence}", name="claro_admin_competence_delete", options={"expose"=true})
     * @EXT\Method("GET")
     *
     * @param Competence $competence
     *
     * Delete a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCompetenceAction(CompetenceNode $competence)
    {
        $id =$competence->getId();
    	$this->cptmanager->delete($competence);

        return new JsonResponse(array('id' => $id));
    }

    /**
     * @EXT\Route("/move/{competence}", name="claro_admin_competence_move_form")
     * @EXT\Method("GET")
     * @EXT\Template()
     * @param Competence $competence
     *
     * move a competence 
     *
     */
    public function competenceMoveFormAction(CompetenceNode $competence)
    {
    	$competences = $this->cptmanager->getHierarchyNameNoHtml($competence);
        return array(
        	'cpt' => $competence,
        	'competences' => $competences
        );
    }

    /**
     * @EXT\Route("/move/{parent}/add", name="claro_admin_competence_move",options={"expose"=true})
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"multipleIds" = true, "name" = "competences"}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceMoveForm.html.twig")
     * @param Competence $competences
     *
     * move a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveCompetenceAction(array $competences,CompetenceNode $parent)
    {	
    	if($this->cptmanager->move($competences,$parent)) {
    	   return new Response(200);
    	}
    }
    /**
     * @EXT\Route("/link/form/{competence}/", name="claro_admin_competences_link_form")
     * @Ext\Template()
     */
    public function competenceLinkFormAction(CompetenceNode $competence) 
    {
        return array(
            'competences' => $this->cptmanager->getExcludeHiearchy($competence),
            'cpt' => $competence
        );
    }
    /**
     * @EXT\Route("/link/{parent}/", name="claro_admin_competences_link",options={"expose"=true})
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\Competence:competenceReferential.html.twig")
     */
    public function competenceLinkAction(CompetenceNode $parent)
    {        
        $competenceId = $this->request->request->get('competence');
        $competence = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceNode')->findOneById($competenceId);
        $this->cptmanager->link($competence, $parent);
        $competences = $this->cptmanager->getHierarchyName($competence);
           
        return array(
            'competences' => $competences,
            'cpt' => $parent
        );
    }

     /**
     * @EXT\Route("/get/{competenceId}", name="claro_admin_competence_full_hierarchy",options={"expose"=true})
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @param Competence $competences
     *
     * get the html structure of the hole Learning outcome
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getFullCompetenceNodeAction($competence)
    {
    	$tree = $this->cptmanager->getHierarchy($competence);
    	return new Response(
        	json_encode(
        		array(
        			'tree' => $tree
        		)
        	),
        	200,
        	array('Content-Type' => 'application/json')
        );
    }

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->toolManager)) {
            return true;
        }

        throw new AccessDeniedException();
    }
} 