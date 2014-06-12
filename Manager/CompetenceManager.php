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
use Claroline\CoreBundle\Persistence\ObjectManager;
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

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "security"     = @DI\Inject("security.context"),
     *     "rm"           = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $security,
        RoleManager $rm
    )
    {
        $this->om = $om;
        $this->security = $security;
        $this->rm = $rm;
    }

    public function getTransversalCompetences()
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
    	return ($this->om->getRepository('ClarolineCoreBundle:Competence\Competence')->getRoots());
    }

    public function getAllHierarchy()
    {
    	if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
    	return ($this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy')->getAllHierarchy());
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
        return true;
    }

    /**
     * Order in a tab all the competences beetween them ([root][parents][childs])
     */
    public function orderHierarchy()
    {
    	$cptHierarchy = $this->getAllHierarchy();
    	$tab = array();
    	foreach ($cptHierarchy as $c ) {
    		$parentId = $c->getParent()->getId();
    		$rootId = $c->getRoot()->getId();
    		$cpt = $c->getCompetence();
    		// initializing the tab , if it's the first time , we create the root node
    		//if is a root node , we create an entry
    		if( !isset($tab[$rootId]) ) {
    			$tab[$rootId] = array();
    		}
    		// if the root node exist for this entry , we check if the parent entry exists
    		// if not we create an parent entry 
    		if (!isset($tab[$rootId][$parentId])) {
    			$tab[$rootId][$parentId] = array();
    		}
    		// at the end the value of the array is the competence who belong to a parent and a root node
    		$tab[$rootId][$parentId][] = $cpt;
    	}
    	return $tab;
    }

    /**
     * @param Competence $competence
     * @param Competence $subCompetence
     * @param Competence $root
     * @param null $workspace
     * @return bool
     */
    public function addSub(Competence $competence,Competence $subCompetence,Competence $root, $workspace = null)
    {    	
    	if($this->add($subCompetence)) {    		
    		$cptHierarchy = new CompetenceHierarchy();
    		$cptHierarchy->setCompetence($subCompetence);
    		$cptHierarchy->setParent($competence);
    		$cptHierarchy->setRoot($root);
    		$this->om->persist($cptHierarchy);
    		$this->om->flush();
    		return true;
    	}
    }
    public function delete(Competence $competence)
    {
    	if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $this->om->remove($competence);
        $this->om->flush();
        return true;
    }

    public function link(array $competences, Competence $parent, Competence $root )
    {
    	$this->om->startFlushSuite();
    	foreach ($competences as $c) {
	    	$cptHierarchy = $this->om->factory('Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy');
			$cptHierarchy->setCompetence($c);
			$cptHierarchy->setParent($parent);
			$cptHierarchy->setRoot($root);
			$this->om->persist($cptHierarchy);
    	}
    	$this->om->endFlushSuite();

		return true;
    }

    public function getHierarchyCompetence (Competence $competence , Competence $root)
    {
    	return true;
    }

    public function getExcludeHiearchy(Competence $competence)
    {
    	return $this->om->getRepository('ClarolineCoreBundle:Competence\Competence')
    		->getExcludeNodeCompetence($competence);
    }

    private function checkUserIsAllowed($permission, AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }
} 