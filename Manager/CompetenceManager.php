<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Competence\Competence;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
//use Claroline\CoreBundle\Entity\Competence\UserCompetence;
//use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.competence_manager")
 */
class CompetenceManager
{
    private $om;
    private $pagerFactory;
    private $router;
    private $templating;
    private $translator;
    private $cptNodeRepo;
    private $cptRepo;
    private $userCptRepo;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory" = @DI\Inject("claroline.pager.pager_factory"),
     *     "router"       = @DI\Inject("router"),
     *     "templating"   = @DI\Inject("templating"),
     *     "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        UrlGeneratorInterface $router,
        TwigEngine $templating,
        TranslatorInterface $translator
    )
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->cptNodeRepo = $om->getRepository('ClarolineCoreBundle:Competence\CompetenceNode');
        $this->cptRepo = $om->getRepository('ClarolineCoreBundle:Competence\Competence');
        $this->userCptRepo = $om->getRepository('ClarolineCoreBundle:Competence\UserCompetence');
    }

    public function getHierarchyByCompetenceNode(
        CompetenceNode $competenceNode
    )
    {
        $cptNodes = $this->cptNodeRepo
            ->findHierarchyByCompetenceNode($competenceNode);
        $competences = array();

        foreach ($cptNodes as $cptNode) {
            $competences[$cptNode->getId()]['name'] = $cptNode->getCompetence()->getName();
            $competences[$cptNode->getId()]['competenceId'] = $cptNode->getCompetence()->getId();

            if ($cptNode->getId() === $competenceNode->getId()) {
                $competences[$cptNode->getId()]['root'] = true;
            } else {
                $competences[$cptNode->getId()]['root'] = false;
            }
        }

        $options = array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'nodeDecorator' => function($node) use (&$competences) {

                return $this->templating->render(
                    'ClarolineCoreBundle:Tool\workspace\competence:competenceNodeHierarchyDisplay.html.twig',
                    array('node' => $node, 'competences' => $competences)
                );
            }
        );
        $htmlTree = $this->cptNodeRepo->childrenHierarchy(
            $competenceNode, /* starting from root nodes */
            false, /* true: load all children, false: only direct */
            $options,
            true
        );
        return $htmlTree;
    }

    public function persistCompetence(Competence $competence)
    {
        $this->om->persist($competence);
        $this->om->flush();
    }

    public function deleteCompetence(Competence $competence)
    {
        $competenceNodes = $this->cptNodeRepo
            ->findCompetenceNodesByCompetence($competence);

        foreach ($competenceNodes as $competenceNode) {
            $this->cptNodeRepo->removeFromTree($competenceNode);
        }
        $this->om->remove($competence);
        $this->om->flush();
    }

    public function createCompetenceNode(
        Competence $competence,
        CompetenceNode $parent = null
    )
    {
        $competenceNode = new CompetenceNode();
        $competenceNode->setCompetence($competence);

        if (is_null($parent)) {
            $competenceNode->setRoot($competenceNode->getId());
        } else {
            $competenceNode->setParent($parent);
            $competenceNode->setRoot($parent->getRoot());
        }
        $this->om->persist($competenceNode);
        $this->om->flush();

        return $competenceNode;
    }

    public function deleteCompetenceNode(CompetenceNode $competenceNode)
    {
        $subCompetenceNodes = $this->cptNodeRepo
            ->findSubCompetenceNodesByCompetenceNode($competenceNode);

        foreach ($subCompetenceNodes as $node) {
            $this->cptNodeRepo->removeFromTree($node);
            $this->om->remove($competenceNode);
        }
        $this->cptNodeRepo->removeFromTree($competenceNode);
        $this->om->remove($competenceNode);
        $this->om->flush();
    }

    
    /**********************************************************************
     **********************************************************************
     **                                                                  **
     **     The code below has to be checked and removed if useless.     **
     **                                                                  **
     **********************************************************************
     **********************************************************************/

//    public function moveCompetenceNode(
//        CompetenceNode $node,
//        CompetenceNode $parent
//    )
//    {
//        $node->setParent($parent);
//        $this->om->persist($node);
//        $this->om->flush();
////        $this->cptNodeRepo->reorder($competenceNode);
//    }
//
//    public function add(Competence $competence, Workspace $workspace = null)
//    {
//        $isPlatform = is_null($workspace) ? true : false;
//        $competence->setIsplatform($isPlatform);
//        $this->om->persist($competence);
//        $this->om->flush();
//        $competenceNode = new CompetenceNode();
//        $competenceNode->setCompetence($competence);
//        $competenceNode->setCptRoot($competenceNode);
//        $this->om->persist($competenceNode);
//        $this->om->flush();
//        return $competenceNode;
//    }
//
//    /**
//     * @param Competence $competence
//     * @param Competence $subCompetence
//     * @param Competence $root
//     * @param null $workspace
//     * @return bool
//     */
//    public function addSub(CompetenceNode $parent, Competence $subCompetence, $workspace = null)
//    {
//    	if($c = $this->add($subCompetence)) {
//    		$c->setParent($parent);
//    		$this->om->flush();
//
//            if (count($users = $this->getUserByCompetenceRoot($parent->getCompetence())) > 0) {
//                $this->subscribeUserToCompetences($users, array($parent->getCompetence()));
//            }
//
//    		return true;
//    	}
//    }
//
//    public function delete(CompetenceNode $competence)
//    {
//        $c = $competence->getCompetence();
//        $this->cptNodeRepo->removeFromTree($competence);
//        $this->om->remove($c);
//        $this->om->flush();
//        return true;
//    }
//
//    public function updateCompetence(CompetenceNode $competence)
//    {
//    	$this->om->flush();
//    	return true;
//    }
//
//    public function move(array $competences, CompetenceNode $parent)
//    {
//    	$this->om->startFlushSuite();
//    	foreach ($competences as $c) {
//	    	$this->cptNodeRepo->persistAsNextSibling($c, $parent);
//			$this->om->flush();
//    	}
//    	$this->om->endFlushSuite();
//
//		return true;
//    }
//
//    public function linkCompetence(Competence $competence, CompetenceNode $parent)
//    {
//        $this->createCompetenceNode($competence, $parent);
//        $this->cptNodeRepo->verify();
//        $this->om->flush();
//    }
//
//    public function getHierarchy(CompetenceNode $competenceNode)
//    {
//        $listCompetences = $this->cptNodeRepo->findHiearchyById($competenceNode);
//        $competences = array();
//
//        foreach ($listCompetences as $c) {
//        	$competences[$c['id']]['name'] = $c['name'];
//        	$competences[$c['id']]['description'] = $c['description'];
//        	$competences[$c['id']]['score'] = $c['score'];
//            $competences[$c['id']]['code'] = $c['code'];
//        }
//
//        $options = array(
//            'decorate' => true,
//            'rootOpen' => '<ul>',
//            'rootClose' => '</ul>',
//            'childOpen' => '<li> <div>',
//            'childClose' => '</div></li>',
//            'nodeDecorator' => function($node) use (&$competences) {
//                return $competences[$node['id']]['name']
//                .'('.$competences[$node['id']]['score'].') <br />'
//                .$competences[$node['id']]['code'].'<div class="">'.
//                $competences[$node['id']]['description'].'</div>';
//            }
//        );
//        $htmlTree = $this->cptNodeRepo->childrenHierarchy(
//            $competenceNode, /* starting from root nodes */
//            false, /* true: load all children, false: only direct */
//            $options,
//            true
//        );
//        return $htmlTree;
//    }
//
//    /**
//     * [subscribeUserToCompetences description]
//     * @param  $users
//     * @param  $competences
//     * @return boolean
//     */
//    public function subscribeUserToCompetencesRoot(array $users,array $competences)
//    {
//        $this->om->startFlushSuite();
//        $tab = $this->getChildrenCompetence($competences);
//        foreach ($users as $u) {
//
//            foreach ($tab as $cpt) {
//                $cptUser = new UserCompetence();
//                $cptUser->setCompetence($cpt);
//                $cptUser->setUser($u);
//                $cptUser->setScore(0);
//                $this->om->persist($cptUser);
//            }
//        }
//        $this->om->endFlushSuite();
//        return true;
//    }
//
//    public function subscribeUserToCompetences(array $users, array $competences)
//    {
//        $this->om->startFlushSuite();
//
//        //@todo FIX ME THIS
//        /*
//        foreach ($users as $u) {
//            foreach ($competences as $competence) {
//                $cptUser = new UserCompetence();
//                $cptUser->setCompetence($competence);
//                $cptUser->setUser($u);
//                $cptUser->setScore(0);
//                $this->om->persist($cptUser);
//            }
//        }
//        */
//
//        $this->om->endFlushSuite();
//        return true;
//    }
//    /**
//     * !!! u can not delete/unsubscribe to a competence if the competence user link exist in an another
//     * Learning outcomes.
//     * @param  array  $users [description]
//     * @return boolean
//     */
//    public function unsubscribeUserToCompetences(array $users, $root)
//    {
//        $rootsId = $this->userCptRepo->findRootCompetences();
//
//        foreach ($users as $user) {
//           $this->userCptRepo->deleteNodeHiearchy($user, $root);
//        }
//        $this->om->flush();
//        return true;
//    }
//
//    public function getCompetencesAssociateUsers(CompetenceNode $competence = null)
//    {
//        $list = is_null($competence) ?
//            $this->userCptRepo->findAll(false, 'competence','asc')
//                :
//            $this->userCptRepo->findHiearchyByNode($competence);
//
//        $orderedList = array();
//
//        foreach ($list as $cu ) {
//            if (!isset($orderedList[$cu->getCompetence()->getName()])) {
//                $orderedList[$cu->getCompetence()->getName()] = array();
//                $orderedList[$cu->getCompetence()->getName()]['id'] = $cu->getCompetence()->getId();
//                $orderedList[$cu->getCompetence()->getName()]['users'] = array();
//            }
//            $orderedList[$cu->getCompetence()->getName()]['users'][] = $cu;
//        }
//        return $orderedList;
//    }
//
//    public function getUserByCompetenceRoot(Competence $competence)
//    {
//        $result = $this->userCptRepo->findByCompetence($competence);
//        $result = count($result) > 0 ? $result : array();
//
//        return $result;
//    }
//
//    public function getUserCompetenceByWorkspace(Workspace $workspace, User $user)
//    {
//        return $this->userCptRepo->findByWorkspace($workspace, $user);
//    }
//
//    public function getCompetenceByWorkspace(Workspace $workspace)
//    {
//        return $this->cptNodeRepo->findByWorkspace($workspace);
//    }
//
//    private function getChildrenCompetence(array $roots)
//    {
//        $listCompetences = $this->cptNodeRepo->findFullHiearchyById($roots);
//        if(count($listCompetences) > 0) {
//            return $listCompetences;
//        }
//        return array();
//    }
//
//    /**
//     * Test if the node A ( link node) is not a parent of node B
//     * To understand it correctly , please read Nested Tree therory.
//     */
//    private function isParent(CompetenceNode $nodeA , CompetenceNode $nodeB)
//    {
//        if($nodeA->getLft() > $nodeB->getLft() && $nodeA->getRgt() < $nodeB->getRgt() ) {
//            return true;
//        }
//        return false ;
//    }


    /******************************************
     * Access to CompetenceRepository methods *
     ******************************************/

    public function getLinkableAdminCompetences(CompetenceNode $competenceNode)
    {
        return $this->cptRepo->findLinkableAdminCompetences($competenceNode);
    }

    public function getLinkableWorkspaceCompetences(
        Workspace $workspace,
        CompetenceNode $competenceNode
    )
    {
        return $this->cptRepo
            ->findLinkableWorkspaceCompetences($workspace, $competenceNode);
    }

    public function getCompetenceById($competenceId)
    {
        return $this->cptRepo->findOneById($competenceId);
    }

    public function getAdminCompetences(
        $orderedBy = 'name',
        $order = 'ASC',
        $page = 1,
        $max = 20
    )
    {
        $sompetences = $this->cptRepo->findAdminCompetences($orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($sompetences, $page, $max);
    }

    public function getWorkspaceCompetences(
        Workspace $workspace,
        $orderedBy = 'name',
        $order = 'ASC',
        $page = 1,
        $max = 20
    )
    {
        $sompetences = $this->cptRepo
            ->findWorkspaceCompetences($workspace, $orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($sompetences, $page, $max);
    }


    /**********************************************
     * Access to CompetenceNodeRepository methods *
     **********************************************/
    
    /**
     * @return Get all the root competence whas no parent and no workspace
     */
    public function getRootCompetenceNodes(Workspace $workspace = null)
    {
        if (is_null($workspace)) {

            return $this->cptNodeRepo->findAdminRootCompetenceNodes();
        } else {

            return $this->cptNodeRepo->findWorkspaceRootCompetenceNodes($workspace);
        }
    }

    public function getCompetenceNodeById($competenceNodeId)
    {
        return $this->cptNodeRepo->findOneById($competenceNodeId);
    }

//    public function getHierarchyNameNoHtml(CompetenceNode $competenceNode)
//    {
//        return $this->cptNodeRepo
//            ->findHierarchyByCompetenceNode($competenceNode);
//    }
} 