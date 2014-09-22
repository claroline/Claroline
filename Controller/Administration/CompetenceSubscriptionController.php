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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Manager\ToolManager;
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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CompetenceSubscriptionController
{
    private $cptmanager;
    private $request;
    private $userManager;
    private $sc;
    private $adminTool;
    /**
     * @DI\InjectParams({
     *     "request"            = @DI\Inject("request"),
     *     "router"             = @DI\Inject("router"),
     *     "cptmanager"			= @DI\Inject("claroline.manager.competence_manager"),
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        CompetenceManager $cptmanager,
        UserManager $userManager,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager
    )
    {
        $this->request = $request;
        $this->router = $router;
        $this->cptmanager = $cptmanager;
        $this->userManager = $userManager;
        $this->sc = $securityContext;
        $this->toolManager = $toolManager->getAdminToolByName('competence_subscription');
    }

    /**
     * @EXT\Route("/menu/",
     *  name="claro_admin_competences_subscription_menu")
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
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\Competence",
     *      options={"multipleIds" = true, "name" = "competences"}
     * )
     *
     * @EXT\Template()
     **/
    public function subscriptionAction(array $competences)
    {
        $users = array();
        foreach ($competences as $c) {
            $users = array_merge($users, $this->cptmanager->getUserByCompetenceRoot($c));
        }

        if (count($users)) {
    	    $pager = $this->userManager->getAllUsersExcept(1, 20, 'id', null, $users);
        } else {
            $pager = $this->userManager->getAllUsers(1);
        }

    	return array(
    		'competences' => $competences,
    		'users' => $pager,
    		'search' => ''
    	);
    }

    /**
     * @EXT\Route("/subscription/users",
     * name="claro_admin_competence_subcription_users",options={"expose"=true})
     * @EXT\ParamConverter(
     *     "competences",
     *      class="ClarolineCoreBundle:Competence\Competence",
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

    /**
     * @EXT\Route("/unsubscription/users/{root}",
     * name="claro_admin_competence_unsubscription_users",options={"expose"=true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:Competence\UserCompetence",
     *      options={"multipleIds" = true, "name" = "users"}
     * )
     * @EXT\ParamConverter(
     *     "root",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"id" = "root", "strictId" = true}
     * )
     * @EXT\Template()
     **/
    public function unsubscriptionUsersAction(array $users,CompetenceNode $root)
    {
    	$this->cptmanager->unsubscribeUserToCompetences($users, $root);

        return new Response(200);
    }

    /**
     * @EXT\Route("/subscription/users/competences",
     * name="claro_admin_competences_list_users",options={"expose"=true})
     * @EXT\Template()
     **/
    public function listUsersAction()
    {
    	$listUsersCompetences =
    	$this->cptmanager->getCompetencesAssociateUsers();

    	return array('listUsers' => $listUsersCompetences);
    }

    /**
     * @EXT\Route("subscription/users/competences/show/{competenceId}",
     *  name="claro_admin_competences_subscription_details")
     * @EXT\ParamConverter(
     *      "competence",
     *      class="ClarolineCoreBundle:Competence\CompetenceNode",
     *      options={"id" = "competenceId", "strictId" = true}
     * )
     * @EXT\Template()
     * @param Competence $competence
     */

    public function showSubscriptionAction($competence)
    {
        $this->checkOpen();
        $listUsersCompetences =
        $this->cptmanager->getCompetencesAssociateUsers($competence);
        $tree = $this->cptmanager->getHierarchy($competence);

        return array(
            'listUsers' => $listUsersCompetences,
            'cpt' => $competence,
            'tree' => $tree
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
