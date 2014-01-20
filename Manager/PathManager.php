<?php

namespace Innova\PathBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Claroline\CoreBundle\Manager\ResourceManager;
use Innova\PathBundle\Entity\Path;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Path Manager
 * Manages life cycle of paths
 * @author Innovalangues <contact@innovalangues.net>
 *
 */
class PathManager
{
    /**
     * Current entity manage for data persist
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;
    
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request $request
     */
    protected $request;

    /**
     * claro resource manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * innova nondigitalresource manager
     * @var \Innova\PathBundle\Manager\NonDigitalResourceManager
     */
    protected $nonDigitalResourceManager;

    /**
     * innova step manager
     * @var \Innova\PathBundle\Manager\StepManager
     */
    protected $stepManager;
    
    /**
     * Current security context
     * @var \Symfony\Component\Security\Core\SecurityContext $security
     */
    protected $security;
    
    /**
     * Authenticated user
     * @var \Claroline\CoreBundle\Entity\User\User $user
     */
    protected $user;
    
    /**
     * Class constructor - Inject required services
     * @param EntityManager $entityManager
     * @param SecurityContext $securityContext
     */
    public function __construct(
        ObjectManager             $objectManager, 
        SecurityContext           $securityContext, 
        ResourceManager           $resourceManager, 
        NonDigitalResourceManager $nonDigitalResourceManager,
        StepManager               $stepManager
    )
    {
        $this->om = $objectManager;
        $this->resourceManager = $resourceManager;
        $this->stepManager = $stepManager;
        $this->security = $securityContext;
        $this->nonDigitalResourceManager = $nonDigitalResourceManager;
        
        // Retrieve current user
        $this->user = $this->security->getToken()->getUser();
    }
    
    /**
     * Inject current request
     * Request is not injected in class constructor to have current request each time we call this service
     * @param Request $request
     * @return \Innova\PathBundle\Manager\PathManager
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
        
        return $this;
    }
    
    public function getResourceType()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('innova_path');
    }
    
    public function getWorkspace($workspaceId)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
    }
    
    /**
     * Create a new path
     * @param  \Innova\PathBundle\Entity\Path $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @return \Innova\PathBundle\Entity\Path
     */
    public function create(Path $path, AbstractWorkspace $workspace)
    {
        // Check if JSON structure is built
        $structure = $path->getStructure();
        if (empty($structure)) {
            // Initialize path structure
            $path->initializeStructure();
        }
        
        // Persist Path
        $this->om->persist($path);
        $this->om->flush();

        // Create a new resource node
        $parent = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        $path = $this->resourceManager->create($path, $this->getResourceType(), $this->user, $workspace, $parent, null);
        
        return $path;
    }
    
    /**
     * Edit existing path
     * @param  \Innova\PathBundle\Entity\Path $path
     * @return \Innova\PathBundle\Entity\Path
     */
    public function edit(Path $path)
    {
        // Check if JSON structure is built
        $structure = $path->getStructure();
        if (empty($structure)) {
            // Initialize path structure
            $path->initializeStructure();
        }

        // Set path as modified (= need publishment to be able to play path with new modifs)
        $path->setModified(true);
        $this->om->persist($path);
        
        // Update resource node if needed
        $resourceNode = $path->getResourceNode();
        if ($path->getName() !== $resourceNode->getName()) {
            // Path name as changed => rename linked resource node
            $resourceNode->setName($path->getName());
            $this->om->persist($resourceNode);
        }
        
        $this->om->flush();
        
        return $path;
    }
    
    /**
     * Delete path
     * @return boolean
     * @throws \Exception
     * @throws NotFoundHttpException
     */
    public function delete()
    {
        $isDeleted = false;
        
        // Load current path
        $path = $this->om->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($this->request->get('id'));
        if (!empty($path)) {
            // Path found
            $pathCreator = $path->getResourceNode()->getCreator();
            
            // Check if current user can delete this path
            if ($pathCreator == $this->user) {
                // User can delete current path
                $this->om->remove($path->getResourceNode());
                $this->om->flush();
            
                $isDeleted = true;
            }
            else {
                // User can't delete path from other users
                throw new \Exception('You can delete only your own paths.');
            }
        }
        else {
            // Path not found
            throw new NotFoundHttpException('The path you want to delete can not be found.');
        }
        
        return $isDeleted;
    }

    /**
     * Find all paths for a workspace
     * @return array
     */
    public function findAllFromWorkspace($workspace)
    {
        $paths = array();
        if (is_object($this->user)) {
            $paths["me"] = $this->om->getRepository('InnovaPathBundle:Path')->findAllByWorkspaceByUser($workspace, $this->user);
            $paths["others"] = $this->om->getRepository('InnovaPathBundle:Path')->findAllByWorkspaceByNotUser($workspace, $this->user);
        }
        else {
            $paths["others"] = $this->om->getRepository('InnovaPathBundle:Path')->findAllByWorkspace($workspace);
        }
        
        return $paths;
    }

    /**
     * Deploy a Path
     * @return boolean
     */
    public function deploy()
    {
        // Récupération vars HTTP
        $pathId = $this->request->get('pathId');
        $path = $this->om->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);

        // On récupère la liste des steps avant modification pour supprimer ceux qui ne sont plus utilisés. TO DO : suppression
        $steps = $this->om->getRepository('InnovaPathBundle:Step')->findByPath($path->getId());
        // initialisation array() de steps à ne pas supprimer. Sera rempli dans la function JSONParser
        $stepsToNotDelete = array();
        $excludedResourcesToResourceNodes = array();

        // JSON string to Object - Récupération des childrens de la racine
        $json = json_decode($path->getStructure());
        $json_root_steps = $json->steps;

        // Récupération Workspace courant
        $workspaceId = $this->request->get('workspaceId');
        $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        // lancement récursion
        $this->JSONParser($json_root_steps, $this->user, $workspace, $path->getResourceNode()->getParent(), 0, null, 0, $path, $stepsToNotDelete, $excludedResourcesToResourceNodes);

        // On nettoie la base des steps qui n'ont pas été réutilisé et les step2resourceNode associés
        foreach ($steps as $step) {
           if (!in_array($step->getId(),$stepsToNotDelete)) {
                $this->om->remove($step);
            }
        }

        // Mise à jour des resourceNodeId dans la base.
        $json = json_encode($json);
        $path->setStructure($json);
        $path->setDeployed(true);
        $path->setModified(false);
        $this->om->flush();

        return true;
    }

    /**
     * private _jsonParser function
     *
     * @param is_object($steps)          $steps          step of activity
     * @param is_object($user)           $user           user of activity
     * @param is_object($workspace)      $workspace      workspace of activity
     * @param is_object($pathsDirectory) $pathsDirectory pathsDirectory of activity
     * @param is_object($parent)         $parent         parent of activity
     * @param is_object($order)          $order          order of activity
     * @param is_object($path)           $path           path of activity
     *
     * @return array
     *
     */
    private function JSONParser($steps, $user, $workspace, $pathsDirectory, $lvl, $parent, $order, $path, &$stepsToNotDelete, &$excludedResourcesToResourceNodes)
    {
        foreach ($steps as $step) {
            $order++;

            //  mise à jour du step 
            $currentStep = $this->stepManager->edit($step->resourceId, $step, $path, $parent, $lvl, $order);

            // STEPSTONOT DELETE ARRAY UPDATE  - le step ne sera pas supprimé.
            $stepsToNotDelete[] = $currentStep->getId();

            // mise à jour du step dans le JSON
            $step->resourceId = $currentStep->getId();

            // STEP'S RESOURCES MANAGEMENT
            $currentStep2resourceNodes = $currentStep->getStep2ResourceNodes();
            $step2resourceNodesToNotDelete = array();

            $resourceOrder = 0;
            foreach ($step->resources as $resource) {
                $resourceOrder++;
                // Gestion des ressources non digitales
                if (!$resource->isDigital) {
                    $nonDigitalResource = $this->nonDigitalResourceManager->edit($workspace, $resource->resourceId, $resource->name, $resource->description, $resource->subType);
                    // update JSON
                    $resource->resourceId = $nonDigitalResource->getResourceNode()->getId();
                }

                $excludedResourcesToResourceNodes[$resource->id] = $resource->resourceId;
                $step2ressourceNode = $this->stepManager->editResourceNodeRelation($currentStep, $resource->resourceId, false, $resource->propagateToChildren, $resourceOrder);
                $step2resourceNodesToNotDelete[] = $step2ressourceNode->getId();
            }

            // Gestion des ressources exclues
            foreach ($step->excludedResources as $excludedResource) {
                $step2ressourceNode = $this->stepManager->editResourceNodeRelation($currentStep, $excludedResourcesToResourceNodes[$excludedResource], true, false, $resourceOrder);
                $step2resourceNodesToNotDelete[] = $step2ressourceNode->getId();
            }

            // Suppression des Step2ResourceNode inutilisés
            foreach ($currentStep2resourceNodes as $currentStep2resourceNode) {
                if (!in_array($currentStep2resourceNode->getId(),$step2resourceNodesToNotDelete)) {
                    $this->om->remove($currentStep2resourceNode);
                }
            }
            
            //$this->om->flush();
            // récursivité sur les enfants possibles.
            $this->JSONParser($step->children, $user, $workspace, $pathsDirectory, $lvl+1, $currentStep, 0, $path, $stepsToNotDelete, $excludedResourcesToResourceNodes);
        }

        $this->om->flush();
    }
}