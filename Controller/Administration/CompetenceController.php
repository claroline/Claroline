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
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
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
class CompetenceController {

    private $formFactory;
    private $cptmanager;
    private $request;
    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "cptmanager"			= @DI\Inject("claroline.manager.competence_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        RouterInterface $router,
        CompetenceManager $cptmanager
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->cptmanager = $cptmanager;
    }

     /**
     * @EXT\Route("/show", name="claro_admin_competences", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competences.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function competenceShowAction()
    {
    	$competences = $this->cptmanager->getTransversalCompetences();
    	$form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);

    	return array(
    		'cpt' => $competences,
    		'form' => $form->createView()
    	);
    }

    /**
     * @EXT\Route("/show/referential/{competenceId}", name="claro_admin_competence_show_referential",options={"expose"=true})
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @param Competence $competence
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competenceReferential.html.twig")
     *
     * Show all the hiearchy from a competence
     *
     */
    public function competenceShowHierarchy($competence)
    {
    	$form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);
        $form->handleRequest($this->request);
        $competences = $this->cptmanager->getHierarchyName($competence);

        if ($form->isValid()) {        	
	        $subCpt = $form->getData();

	        if($this->cptmanager->addSub($competence, $subCpt)) {
        	    return new RedirectResponse(
                $this->router->generate('claro_admin_competences')
	        	);
        	} else {
        		throw new Exception("no written", 1);
        	}
        }  
           
        return array(
        	'form' => $form->createView(),
        	'competences' => $competences,
            'cpt' => $competence
        );
    }

     /**
     * @EXT\Route("/new", name="claro_admin_competence_form")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competenceForm.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function competenceFormAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);

        return array(
        	'form' => $form->createView(),
        	'route' => 'claro_admin_competence_form'
        );
    }

     /**
     * @EXT\Route("/add", name="claro_admin_competence_add")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competenceForm.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function competenceAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE, array());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $competence = $form->getData();
            if($competence = $this->cptmanager->add($competence)) {
            	return new Response(
            	json_encode(
            		array(
            			'id' => $competence->getId(),
            			 'name' => $competence->getCompetence()->getName()
            			)
            		),
                	200,
                	array('Content-Type' => 'application/json')
                );
            } else {
            	throw new Exception("no written", 1);
            	
            }
        } 
        return array(
        	'form' => $form->createView(),
        	'route' => 'claro_admin_competence_add'
        );
    }

    /**
     * @EXT\Route("/addsubcpt/{competenceId}", name="claro_admin_competence_add_sub",options={"expose"=true})
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @param Competence $competence
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competenceForm.html.twig")
     *
     * Add a sub competence
     *
     */
    public function subCompetenceAction($competence)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {        	
            $subCpt = $form->getData();
            if($this->cptmanager->addSub($competence, $subCpt)) {
            	    return new RedirectResponse(
                    $this->router->generate('claro_admin_competences')
                );
            	} else {
            		throw new Exception("no written", 1);
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
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @param Competence $competence
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competenceForm.html.twig")
     *
     * Add a sub competence
     *
     */
    public function competenceModifyAction($competence)
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
     * @EXT\Route("/delete/{competenceId}", name="claro_admin_competence_delete")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     *
     * @param Competence $competence
     *
     * Delete a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCompetenceAction($competence)
    {
    	if($this->cptmanager->delete($competence)) {
    	    return new RedirectResponse(
            $this->router->generate('claro_admin_competences')
        );
        	} else {
    		throw new Exception("no written", 1);
    	}
    }

    /**
     * @EXT\Route("/move/{competenceId}", name="claro_admin_competence_move_form")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competenceMoveForm.html.twig")
     * @param Competence $competence
     *
     * move a competence
     *
     */
    public function moveCompetenceFormAction($competence)
    {
    	$competences = $this->cptmanager->getHierarchyNameNoHtml($competence);
        return array(
        	'cpt' => $competence,
        	'competences' => $competences
        );
    }

    /**
     * @EXT\Route("/move/{parentId}/add", name="claro_admin_competence_move",options={"expose"=true})
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"multipleIds" = true, "name" = "competences"}
     * )
     * @EXT\ParamConverter(
     *      "parent",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"id" = "parentId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\competence:competenceMoveForm.html.twig")
     * @param Competence $competences
     *
     * move a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveCompetenceAction(array $competences, $parent)
    {	
    	if($this->cptmanager->move($competences,$parent)) {
    	   return new Response(200);
     
        	} else {
    		throw new \Exception("no written", 1);
    	}
    }

     /**
     * @EXT\Route("/get/{competenceId}", name="claro_admin_competence_full_hierarchy",options={"expose"=true})
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceHierarchy",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @param Competence $competences
     *
     * get the html structure of the hole Learning outcome
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getFullCompetenceHierarchyAction($competence)
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

    private function checkUserIsAllowed($permission, Workspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }
} 