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
     *     "router"         = @DI\Inject("router")
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

    public function getCompetenceHiearchy(CompetenceHierarchy $competence)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
        $options = array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'nodeDecorator' => function($node) {
                return '<a href='
                .$this->router->generate('claro_admin_competence_add_sub',array('competenceId' => $node['id']))
                .'">'.$node['id'].'</a>';
            }
        );
        $htmlTree = $repo->childrenHierarchy(
            $competence, /* starting from root nodes */
            true, /* true: load all children, false: only direct */
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

    public function link(array $competences, CompetenceHierarchy $parent)
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

    public function getHierarchyCompetence (Competence $competence , Competence $root)
    {
    	return true;
    }

    public function getExcludeHiearchy(CompetenceHierarchy $competence)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:Competence\CompetenceHierarchy');
        $exclude = $repo->excludeHierarchyNode($competence);
        return $exclude;
    }

    private function checkUserIsAllowed($permission, AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted($permission, $workspace)) {
            throw new AccessDeniedException();
        }
    }
} 