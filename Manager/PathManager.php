<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Step2ResourceNode;
use Innova\PathBundle\Entity\NonDigitalResource;

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
     * @var \Doctrine\ORM\EntityManagerEntity Manager $em
     */
    protected $em;
    
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request $request
     */
    protected $request;
    
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
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext)
    {
        $this->em = $entityManager;
        $this->security = $securityContext;
        
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
    
    /**
     * Create a new path
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function create()
    {
        // Get current workspace
        $workspaceId = $this->request->get('workspaceId');
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);
        
        $directory = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneByName("_paths");
        if (!$directory) {
            // Create path directory
            $directory = $this->createDirectory($workspace);
        }
        
        // Create resource node
        $resourceNode = $this->createResourceNode($workspace, $directory);
        
        // Create path
        $newPath = new Path();
        $newPath->setPath($this->request->get('path'));
        $newPath->setDescription($this->request->get('pathDescription'));
        
        // Link resource node to path
        //$newPath->setResourceNode($resourceNode);
        
        // Persist data
        //$this->em->persist($resourceNode);
        $this->em->persist($newPath);
        $this->em->flush();
        
        return $resourceNode;
    }
    
    /**
     * Create new resource node
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $directory
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    protected function createResourceNode($workspace, $directory)
    {
        $resourceNode = new ResourceNode();
        $resourceNode->setClass('Innova\PathBundle\Entity\Path');
        $resourceNode->setCreator($this->user);
        $resourceNode->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('path'));
        $resourceNode->setWorkspace($workspace);
        $resourceNode->setParent($directory);
        $resourceNode->setMimeType('');
        $resourceNode->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));
        $resourceNode->setName($this->request->get('pathName'));
        
        return $resourceNode;
    }
    
    /**
     * Create path directory in workspace
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    protected function createDirectory(AbstractWorkspace $workspace)
    {
        $directory = new ResourceNode();
        
        $directory->setName("_paths");
        $directory->setClass("Claroline\CoreBundle\Entity\Resource\Directory");
        $directory->setCreator($this->user);
        $directory->setWorkspace($workspace);
        $directory->setMimeType("custom/directory");
        
        // TODO : remove hard code to ID
        $directory->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(2));
        
        $root = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        $directory->setParent($root);
        
        // TODO : remove hard code to ID
        $directory->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(7));
        
        $this->em->persist($directory);
        $this->em->flush();
        
        return $directory;
    }
    
    /**
     * Edit existing path
     * @return string|null
     * @throws NotFoundHttpException
     */
    public function edit()
    {
        $pathId = null;
        
        // Load current path
        $path = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($this->request->get('id'));
        if ($path) {
            // Path found
            // Update resource node 
            $resourceNode = $path->getResourceNode();
            $resourceNode->setName($this->request->get('pathName'));
            $this->em->persist($resourceNode);
        
            // Update path
            $path->setPath($this->request->get('path'));
            $path->setDescription($this->request->get('pathDescription'));
            $path->setModified(true);
            $this->em->persist($path);
            
            // Write data into DB
            $this->em->flush();
        
            $pathId = $resourceNode->getId();
        }
        else {
            // Path not found
            throw new NotFoundHttpException('The path you want to edit does not exist.');
        }
        
        return $pathId;
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
        $path = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($this->request->get('id'));
        if (!empty($path)) {
            // Path found
            $pathCreator = $path->getResourceNode()->getCreator();
            
            // Check if current user can delete this path
            if ($pathCreator == $this->user) {
                // User can delete current path
                $this->em->remove($path->getResourceNode());
                $this->em->flush();
            
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
     * Check if wanted name is unique for current user and current Workspace
     * @param string $name
     * @return string
     */
    public function checkNameIsUnique($name)
    {
        // Create query
        $dql  = 'SELECT COUNT(p) ';
        $dql .= 'FROM Innova\PathBundle\Entity\Path AS p ';
        $dql .= 'LEFT JOIN p.resourceNode AS r '; // Join to resource to access path name and creator
        $dql .= 'LEFT JOIN r.workspace AS w ';
        $dql .= 'WHERE r.creator = :user '; // Current User
        $dql .= '  AND w.id = :workspaceId '; // Current Workspace
        $dql .= '  AND r.name = :pathName ';
        $query = $this->em->createQuery($dql);
        
        // Set query parameters
        $query->setParameter('user', $this->user);
        $query->setParameter('pathName', trim($name));
        $query->setParameter('workspaceId', $this->request->get('workspaceId'));

        // Get results
        $count = $query->getSingleScalarResult();
        
        $return = true;
        if (!empty($count) && $count > 0) {
            // A path already have wanted name
            $return = false;
        }
        
        return $return;
    }

    /**
     * Find all paths for a workspace
     * @return array
     */
    public function findAllFromWorkspace($workspace)
    {

        $paths = array();

        $paths["me"] = $this->em->getRepository('InnovaPathBundle:Path')->findAllByWorkspaceByUser($workspace, $this->user);
        $paths["others"] = $this->em->getRepository('InnovaPathBundle:Path')->findAllByWorkspaceByNotUser($workspace, $this->user);
        
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
        $path = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);

        // On récupère la liste des steps avant modification pour supprimer ceux qui ne sont plus utilisés. TO DO : suppression
        $steps = $this->em->getRepository('InnovaPathBundle:Step')->findByPath($path->getId());
        // initialisation array() de steps à ne pas supprimer. Sera rempli dans la function JSONParser
        $stepsToNotDelete = array();
        $excludedResourcesToResourceNodes = array();

        // JSON string to Object - Récupération des childrens de la racine
        $json = json_decode($path->getPath());
        $json_root_steps = $json->steps;

        // Récupération Workspace courant
        $workspaceId = $this->request->get('workspaceId');
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        // lancement récursion
        $this->JSONParser($json_root_steps, $this->user, $workspace, $path->getResourceNode()->getParent(), 0, null, 0, $path, $stepsToNotDelete, $excludedResourcesToResourceNodes);

        // On nettoie la base des steps qui n'ont pas été réutilisé et les step2resourceNode associés
        foreach ($steps as $step) {
           if (!in_array($step->getId(),$stepsToNotDelete)) {
                $this->em->remove($step);
            }
        }

        // Mise à jour des resourceNodeId dans la base.
        $json = json_encode($json);
        $path->setPath($json);
        $path->setDeployed(true);
        $path->setModified(false);
        $this->em->flush();

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

            // CLARO_STEP MANAGEMENT
            if ($step->resourceId == null) {
                $currentStep = new Step();
                
            } else {
                $currentStep = $this->em->getRepository('InnovaPathBundle:Step')->findOneById($step->resourceId);
            }

            // STEPSTONODELETE ARRAY UPDATE
            $stepsToNotDelete[] = $currentStep->getId();

            // CLARO STEP ATTRIBUTES UPDATE
            $currentStep->setPath($path);
            $currentStep->setName($step->name);
            $currentStep->setStepOrder($order);
            $stepType = $this->em->getRepository('InnovaPathBundle:StepType')->findOneById($step->type);
            $currentStep->setStepType($stepType);
            $stepWho = $this->em->getRepository('InnovaPathBundle:StepWho')->findOneById($step->who);
            $currentStep->setStepWho($stepWho);
            $stepWhere = $this->em->getRepository('InnovaPathBundle:StepWhere')->findOneById($step->where);
            $parent = $this->em->getRepository('InnovaPathBundle:Step')->findOneById($parent);
            $currentStep->setParent($parent);
            $currentStep->setLvl($lvl);
            $currentStep->setStepWhere($stepWhere);
            $currentStep->setDuration(new \DateTime("00-00-00 ".intval($step->durationHours).":".intval($step->durationMinutes).":00"));
            $currentStep->setExpanded($step->expanded);
            $currentStep->setWithTutor($step->withTutor);
            $currentStep->setWithComputer($step->withComputer);
            $currentStep->setInstructions($step->instructions);
            $currentStep->setImage($step->image);

            $this->em->persist($currentStep);
            $this->em->flush();

            // JSON_STEP UPDATE
            $step->resourceId = $currentStep->getId();

            // STEP'S RESOURCES MANAGEMENT
            $currentStep2resourceNodes = $currentStep->getStep2ResourceNodes();
            $step2resourceNodesToNotDelete = array();

            $resourceOrder = 0;
            foreach ($step->resources as $resource) {
                $resourceOrder++;

                // Gestion des ressources non digitales
                if (!$resource->isDigital) {
                    if ($resource->resourceId == null){
                        $resourceNode = new ResourceNode();
                        $nonDigitalResource = new NonDigitalResource();
                        $resourceNode->setClass("Innova\PathBundle\Entity\NonDigitalResource");
                        $resourceNode->setCreator($user);
                        $resourceNode->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("non_digital_resource"));
                        $resourceNode->setWorkspace($workspace);
                        $resourceNode->setParent($pathsDirectory);
                        $resourceNode->setMimeType("");
                        $resourceNode->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(3));
                    }
                    else {
                        $resourceNode = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resource->resourceId);
                        $nonDigitalResource = $this->em->getRepository('InnovaPathBundle:NonDigitalResource')->findOneByResourceNode($resourceNode);
                    }

                    $resourceNode->setName($resource->name);
                    $this->em->persist($resourceNode);
                    $nonDigitalResource->setNonDigitalResourceType($this->em->getRepository('InnovaPathBundle:NonDigitalResourceType')->findOneByName($resource->subType));
                    $nonDigitalResource->setResourceNode($resourceNode);
                    $nonDigitalResource->setDescription($resource->description);
                    $this->em->persist($nonDigitalResource);
                   
                    $this->em->flush();

                    $resource->resourceId = $resourceNode->getId();
                }

                $excludedResourcesToResourceNodes[$resource->id] = $resource->resourceId;
                $step2ressourceNode = $this->em->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneBy(array (
                    'step' => $currentStep, 
                    'resourceNode' => $resource->resourceId,
                    'excluded' => false,
                ));
                
                if (!$step2ressourceNode) {
                    $step2ressourceNode = new Step2ResourceNode();
                }

                $step2resourceNodesToNotDelete[] = $step2ressourceNode->getId();
                $step2ressourceNode->setResourceNode($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resource->resourceId));
                $step2ressourceNode->setStep($currentStep);
                $step2ressourceNode->setExcluded(false);
                $step2ressourceNode->setPropagated($resource->propagateToChildren);
                $step2ressourceNode->setResourceOrder($resourceOrder);
                $this->em->persist($step2ressourceNode);
            }

            // STEP'S EXCLUDED RESOURCES MANAGEMENT
            foreach ($step->excludedResources as $excludedResource) {
                // boucler sur les ressourcesnodes exclues en base et les comparer à ce qu'il y a dans le JSON

                $step2ressourceNode = $this->em->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneBy(array(
                    'step' => $currentStep,
                    'resourceNode' => $excludedResourcesToResourceNodes[$excludedResource],
                    'excluded' => true,
                ));
                
                if (!$step2ressourceNode) {
                    $step2ressourceNode = new Step2ResourceNode();
                }
                
                $step2resourceNodesToNotDelete[] = $step2ressourceNode->getId();
                $step2ressourceNode->setResourceNode($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($excludedResourcesToResourceNodes[$excludedResource]));
                $step2ressourceNode->setStep($currentStep);
                $step2ressourceNode->setExcluded(true);
                $step2ressourceNode->setPropagated(false);
                $step2ressourceNode->setResourceOrder(null);
                $this->em->persist($step2ressourceNode);
            }

            foreach ($currentStep2resourceNodes as $currentStep2resourceNode) {
                if (!in_array($currentStep2resourceNode->getId(),$step2resourceNodesToNotDelete)) {
                    $this->em->remove($currentStep2resourceNode);
                }
            }

            /*
            // TO DO : GESTION DES DROITS
            $right = new ResourceRights();
            $right->setRole($this->em->getRepository('ClarolineCoreBundle:Role')->findOneById(3));
            $right->setResourceNode($resourceNode);
            $this->em->persist($right);
            */
            $this->em->flush();

            // récursivité sur les enfants possibles.
            $this->JSONParser($step->children, $user, $workspace, $pathsDirectory, $lvl+1, $currentStep->getId(), 0, $path, $stepsToNotDelete, $excludedResourcesToResourceNodes);
        }

        $this->em->flush();
    }
}