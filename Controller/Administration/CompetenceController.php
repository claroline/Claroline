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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('competence')")
 */
class CompetenceController
{
    private $formFactory;
    private $competenceManager;
    private $request;
    private $om;
    private $adminTool;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "competenceManager"  = @DI\Inject("claroline.manager.competence_manager"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        Request $request,
        RouterInterface $router,
        CompetenceManager $competenceManager,
        ObjectManager $om
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->competenceManager = $competenceManager;
        $this->om = $om;
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
        $competenceHierarchy = $this->competenceManager
            ->getHierarchyByCompetenceNode($competenceNode);

        return array(
            'competenceHierarchy' => $competenceHierarchy,
            'competenceNode' => $competenceNode
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
        return array('competence' => $competence);
    }
}
