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
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "groupManager"       = @DI\Inject("claroline.manager.group_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "cptmanager"			= @DI\Inject("claroline.manager.competence_manager")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        GroupManager $groupManager,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        Request $request,
        RouterInterface $router,
        CompetenceManager $cptmanager
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->groupManager = $groupManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->cptmanager = $cptmanager;
    }

     /**
     * @EXT\Route("/show", name="claro_admin_competences")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Administration:competences.html.twig")
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function competenceShowAction()
    {
    	$competences = $this->cptmanager->getTransversalCompetences();
    	$tab = $this->cptmanager->orderHierarchy();
    	return array(
    		'cpt' => $competences,
    		'cptHierarchy' => $tab
    	);
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

     /**
     * @EXT\Route("/add", name="claro_admin_competence_add")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration:competenceForm.html.twig")
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
            if($this->cptmanager->add($competence)) {
            	return array('form' => $form->createView());
            } else {
            	throw new Exception("no written", 1);
            	
            }
        } 
        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/addsubcpt/{competenceId}/{rootId}", name="claro_admin_competence_add_sub")
     * @EXT\Method({"GET","POST"})
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\Competence",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "root",
     *      class="ClarolineCoreBundle:Competence\Competence",
     *      options={"id" = "rootId", "strictId" = true}
     * )
     *
     * @param Competence $competence
     * @EXT\Template("ClarolineCoreBundle:Administration:competenceForm.html.twig")
     *
     * Add a sub competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subCompetenceAction($competence, $root)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {        	
            $subCpt = $form->getData();
            if($this->cptmanager->addSub($competence, $subCpt, $root)) {
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
        	'root' => $root
        );
    }

    /**
     * @EXT\Route("/delete/{competenceId}", name="claro_admin_competence_delete")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\Competence",
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
     * @EXT\Route("/link/{competenceId}", name="claro_admin_competence_link")
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\Competence",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:competenceLinkForm.html.twig")
     * @param Competence $competence
     *
     * link a competence
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function linkCompetenceFormAction($competence)
    {
    	$competences = $this->cptmanager->getTransversalCompetences();
        $form = $this->formFactory->create(FormFactory::TYPE_COMPETENCE_LINK, array($competences, true));
    	
        return array(
        	'form' => $form->createView(),
        	'cpt' => $competence,
        );
/*
    	if($this->cptmanager->link($competence)) {
    	    return new RedirectResponse(
            $this->router->generate('claro_admin_competences')
        );
        	} else {
    		throw new \Exception("no written", 1);*/
    	}

} 