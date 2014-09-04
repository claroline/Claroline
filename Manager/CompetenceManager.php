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

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Competence\Competence;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Entity\Competence\UserCompetence;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Claroline\CoreBundle\Repository\CompetenceRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("claroline.manager.competence_manager")
 */
class CompetenceManager {

    private $om;
    private $router;
    private $repoCptH;
    private $repoCptUser;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"       = @DI\Inject("router")
     * })
     */
    public function __construct(
        ObjectManager $om,
        UrlGeneratorInterface $router
    )
    {
        $this->om = $om;
        $this->router = $router;
        $this->repoCptH = $om->getRepository('ClarolineCoreBundle:Competence\CompetenceNode');
        $this->repoCptUser = $om->getRepository('ClarolineCoreBundle:Competence\UserCompetence');
    }

    /**
     * @return Get all the root competence whas no parent and no workspace
     */
    public function getTransversalCompetences(Workspace $workspace = null)
    {
        if( is_null($workspace)) {  
            return $this->repoCptH->getRootCpt($workspace);
        }

        return $this->repoCptH->getRootCptWithWorkspace($workspace);
    }

    public function getHierarchyNameNoHtml(CompetenceNode $competence)
    {
    	$repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceNode');
        $listCompetences = $repo->findHiearchyNameById($competence);
        return ($listCompetences);	
    }
    public function getHierarchyName(CompetenceNode $competence)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceNode');
        $listCompetences = $repo->findHiearchyNameById($competence);
        $competences = array();

        foreach ($listCompetences as $c) {
        	$competences[$c['id']] = $c['name'];
        }

        $options = array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'nodeDecorator' => function($node) use (&$competences) {
                return '<a href='
                .$this->router->generate('claro_admin_competence_modify',array('competenceId' => $node['id']))
                .'>'.$competences[$node['id']].'</a>';
            }
        );
        $htmlTree = $repo->childrenHierarchy(
            $competence, /* starting from root nodes */
            false, /* true: load all children, false: only direct */
            $options,
            true
        );
        return $htmlTree;
    }

    public function add(Competence $competence, Workspace $workspace = null)
    {
        $isPlatform = is_null($workspace) ? true : false;
        $competence->setIsplatform($isPlatform);
        $this->om->persist($competence);
        $this->om->flush();
        $cptHierarchy = new CompetenceNode();
		$cptHierarchy->setCompetence($competence);
		$cptHierarchy->setCptRoot($cptHierarchy);
		$this->om->persist($cptHierarchy);
		$this->om->flush();
        return $cptHierarchy;
    }

    /**
     * @param Competence $competence
     * @param Competence $subCompetence
     * @param Competence $root
     * @param null $workspace
     * @return bool
     */
    public function addSub(CompetenceNode $parent, Competence $subCompetence, $workspace = null)
    {    		
    	if($c = $this->add($subCompetence)) {  
    		$c->setParent($parent);
    		$this->om->flush();

            if (count($users = $this->getUserByCompetenceRoot($parent->getCompetence())) > 0) {
                $this->subscribeUserToCompetences($users, array($parent->getCompetence()));
            }

    		return true;
    	}
    }

    public function delete(CompetenceNode $competence)
    {
        $c = $competence->getCompetence(); 
        $this->repoCptH->removeFromTree($competence);
        $this->om->remove($c);
        $this->om->flush();
        return true;
    }

    public function updateCompetence(CompetenceNode $competence)
    {
    	$this->om->flush();
    	return true;
    }

    public function move(array $competences, CompetenceNode $parent)
    {
    	$this->om->startFlushSuite();
    	foreach ($competences as $c) {
	    	$this->repoCptH->persistAsNextSibling($c, $parent);
			$this->om->flush();
    	}
    	$this->om->endFlushSuite();

		return true;
    }

    public function link(CompetenceNode $parent, CompetenceNode $cptNode)
    {
        $cptHierarchy = $this->add($cptNode->getCompetence());
        if ( $this->isParent($cptNode, $parent)) {
            return false;
        }
        $this->repoCptH->persistAsLastChildOf($cptHierarchy, $parent);
        
        $this->repoCptH->verify();
        $this->om->flush();
        return true;
    }

    public function getHierarchy(CompetenceNode $competence)
    {
        $listCompetences = $this->repoCptH->findHiearchyById($competence);
        $competences = array();

        foreach ($listCompetences as $c) {
        	$competences[$c['id']]['name'] = $c['name'];
        	$competences[$c['id']]['description'] = $c['description'];
        	$competences[$c['id']]['score'] = $c['score'];
            $competences[$c['id']]['code'] = $c['code'];
        }

        $options = array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li> <div>',
            'childClose' => '</div></li>',
            'nodeDecorator' => function($node) use (&$competences) {
                return $competences[$node['id']]['name']
                .'('.$competences[$node['id']]['score'].') <br />'
                .$competences[$node['id']]['code'].'<div class="">'.
                $competences[$node['id']]['description'].'</div>';
            }
        );
        $htmlTree = $this->repoCptH->childrenHierarchy(
            $competence, /* starting from root nodes */
            false, /* true: load all children, false: only direct */
            $options,
            true
        );
        return $htmlTree;
    }

    public function getExcludeHiearchy(CompetenceNode $competence)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceNode');
        $exclude = $repo->excludeHierarchyNode($competence);
        return $exclude;
    }
    
    /**
     * [subscribeUserToCompetences description]
     * @param  $users       
     * @param  $competences
     * @return boolean             
     */
    public function subscribeUserToCompetencesRoot(array $users,array $competences)
    {
        $this->om->startFlushSuite();
        $tab = $this->getChildrenCompetence($competences);
        foreach ($users as $u) {

            foreach ($tab as $cpt) {
                $cptUser = new UserCompetence();
                $cptUser->setCompetence($cpt);
                $cptUser->setUser($u);
                $cptUser->setScore(0);
                $this->om->persist($cptUser);
            }
        }
        $this->om->endFlushSuite();
        return true;
    }

    public function subscribeUserToCompetences(array $users, array $competences)
    {
        $this->om->startFlushSuite();

        //@todo FIX ME THIS
        /*
        foreach ($users as $u) {
            foreach ($competences as $competence) {
                $cptUser = new UserCompetence();
                $cptUser->setCompetence($competence);
                $cptUser->setUser($u);
                $cptUser->setScore(0);
                $this->om->persist($cptUser);
            }
        }
        */

        $this->om->endFlushSuite();
        return true;
    }
    /**
     * !!! u can not delete/unsubscribe to a competence if the competence user link exist in an another
     * Learning outcomes.
     * @param  array  $users [description]
     * @return boolean
     */
    public function unsubscribeUserToCompetences(array $users, $root)
    {
        $rootsId = $this->repoCptUser->getRootCpt();
        
        foreach ($users as $user) {
           $this->repoCptUser->deleteNodeHiearchy($user, $root);
        }
        $this->om->flush();
        return true;
    }

    public function getCompetencesAssociateUsers(CompetenceNode $competence = null)
    {
        $list = is_null($competence) ?
            $this->repoCptUser->findAll(false, 'competence','asc') 
                :
            $this->repoCptUser->findHiearchyByNode($competence);

        $orderedList = array();
        
        foreach ($list as $cu ) {
            if (!isset($orderedList[$cu->getCompetence()->getName()])) {  
                $orderedList[$cu->getCompetence()->getName()] = array();  
                $orderedList[$cu->getCompetence()->getName()]['id'] = $cu->getCompetence()->getId();
                $orderedList[$cu->getCompetence()->getName()]['users'] = array();
            }
            $orderedList[$cu->getCompetence()->getName()]['users'][] = $cu;
        }
        return $orderedList;
    }

    public function getUserByCompetenceRoot(Competence $competence)
    {
        $result = $this->repoCptUser->findByCompetence($competence);
        $result = count($result) > 0 ? $result : array();
        
        return $result;
    }

    public function getUserCompetenceByWorkspace(Workspace $workspace, User $user)
    {
        return $this->repoCptUser->findByWorkspace($workspace, $user);
    }

    public function getCompetenceByWorkspace(Workspace $workspace)
    {
        return $this->repoCptH->findByWorkspace($workspace);
    }

    private function getChildrenCompetence(array $roots)
    {
        $listCompetences = $this->repoCptH->findFullHiearchyById($roots);
        if(count($listCompetences) > 0) {
            return $listCompetences;
        }
        return array();
    }

    /**
     * Test if the node A ( link node) is not a parent of node B
     * To understand it correctly , please read Nested Tree therory.
     */
    private function isParent(CompetenceNode $nodeA , CompetenceNode $nodeB)
    {
        if($nodeA->getLft() > $nodeB->getLft() && $nodeA->getRgt() < $nodeB->getRgt() ) {
            return true;
        }
        return false ;
    }
} 