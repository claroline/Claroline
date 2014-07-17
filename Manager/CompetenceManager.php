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
use Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy;
use Claroline\CoreBundle\Entity\Competence\UserCompetence;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Claroline\CoreBundle\Repository\CompetenceRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("claroline.manager.competence_manager")
 */
class CompetenceManager {

    private $om;
    private $security;
    private $rm;
    private $router;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "security"     = @DI\Inject("security.context"),
     *     "rm"           = @DI\Inject("claroline.manager.role_manager"),
     *     "router"       = @DI\Inject("router")
     * })
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $security,
        RoleManager $rm,
        UrlGeneratorInterface $router
    )
    {
        $this->om = $om;
        $this->security = $security;
        $this->rm = $rm;
        $this->router = $router;
    }

    /**
     * @return Get all the root competence whas no parent and no workspace
     */
    public function getTransversalCompetences()
    {
       $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
       return $repo->getRootCpt();
    }

    public function getHierarchyNameNoHtml(CompetenceHierarchy $competence)
    {
    	$repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
        $listCompetences = $repo->findHiearchyNameById($competence);
        return ($listCompetences);	
    }
    public function getHierarchyName(CompetenceHierarchy $competence)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
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

    public function add(Competence $competence, $workspace = null)
    {
        if(!is_null($workspace)) {
            $this->checkUserIsAllowed('ROLE_ADMIN', $workspace);
        }
        $isPlatform = is_null($workspace) ? true : false;
        $competence->setIsplatform($isPlatform);
        $this->om->persist($competence);
        $this->om->flush();
        $cptHierarchy = new CompetenceHierarchy();
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
    public function addSub(CompetenceHierarchy $competence,Competence $subCompetence, $workspace = null)
    {    		
    	if($c = $this->add($subCompetence)) {  
    		$c->setParent($competence);
    		$this->om->flush();
    		return true;
    	}
    }

    public function delete(CompetenceHierarchy $competence)
    {
    	if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $c = $competence->getCompetence(); 
        $repo = $this->om->getRepository('Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy');
        $repo->removeFromTree($competence);
        $this->om->remove($c);
        $this->om->clear();
        $this->om->flush();
        return true;
    }

    public function updateCompetence(CompetenceHierarchy $competence)
    {
    	$this->om->flush();
    	return true;
    }

    public function move(array $competences, CompetenceHierarchy $parent)
    {
    	$this->om->startFlushSuite();
    	foreach ($competences as $c) {
	    	$repo = $this->om->getRepository('Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy');
	    	$repo->persistAsLastChildOf($c, $parent);
			$this->om->flush();
    	}
    	$this->om->endFlushSuite();

		return true;
    }

    public function getHierarchy(CompetenceHierarchy $competence)
    {
    	$repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
        $listCompetences = $repo->findHiearchyById($competence);
        $competences = array();

        foreach ($listCompetences as $c) {
        	$competences[$c['id']]['name'] = $c['name'];
        	$competences[$c['id']]['description'] = $c['description'];
        	$competences[$c['id']]['score'] = $c['score'];
        }

        $options = array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'nodeDecorator' => function($node) use (&$competences) {
                return $competences[$node['id']]['name']
                .'('.$competences[$node['id']]['score'].')'
                .'<div class="">'.
                $competences[$node['id']]['description'].'</div>';
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

    public function getExcludeHiearchy(CompetenceHierarchy $competence)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
        $exclude = $repo->excludeHierarchyNode($competence);
        return $exclude;
    }
    /**
     * [subscribeUserToCompetences description]
     * @param  $users       
     * @param  $competences
     * @return boolean             
     */
    public function subscribeUserToCompetences($users, $competences)
    {
        $this->om->startFlushSuite();
        $tab = $this->subscribeUsersToChildren($competences);
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
    /**
     * !!! u can not delete/unsubscribe to a competence if the competence user link exist in an another
     * Learning outcomes.
     * @param  array  $users [description]
     * @return boolean
     */
    public function unsubscribeUserToCompetences(array $users, $root)
    {
        $repoCpt = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
        $rootsId = $repoCpt->getRootCpt();
        
        foreach ($users as $user) {
           $this->om->getRepository('ClarolineCoreBundle:Competence\UserCompetence')
           ->deleteNodeHiearchy($user, $root);
        }
        $this->om->flush();
        return true;
    }

    public function getCompetencesAssociateUsers(CompetenceHierarchy $competence = null)
    {
        $list = is_null($competence) ?
            $this->om->getRepository('ClarolineCoreBundle:Competence\UserCompetence')
                ->findAll(false, 'competence','asc') 
                :
            $this->om->getRepository('ClarolineCoreBundle:Competence\UserCompetence')
                ->findHiearchyByNode($competence);

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

    private function subscribeUsersToChildren( array $roots)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
        $listCompetences = $repo->findFullHiearchyById($roots);
        if(count($listCompetences) > 0) {
            return $listCompetences;
        }
        return array();
    }

    private function checkUserIsAllowed($permission, AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }
} 