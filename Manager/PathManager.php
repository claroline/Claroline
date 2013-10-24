<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Resource;
use Innova\PathBundle\Entity\StepType;
use Innova\PathBundle\Entity\StepWho;
use Innova\PathBundle\Entity\StepWhere;
use Innova\PathBundle\Entity\Step2ResourceNode;
use Innova\PathBundle\Entity\NonDigitalResource;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;

class PathManager
{
    protected $em;
    protected $request;
    protected $security;
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
     */
    public function create()
    {
        // Get current workspace
//         $workspaceId = $this->request->get('workspaceId');
//         $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);
        
//         // création du dossier _paths s'il existe pas.
//         if (!$pathsDirectory = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneByName("_paths")) {
//             $pathsDirectory = new ResourceNode();
//             $pathsDirectory->setName("_paths");
//             $pathsDirectory->setClass("Claroline\CoreBundle\Entity\Resource\Directory");
//             $pathsDirectory->setCreator($this->user);
//             $pathsDirectory->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(2));
//             $root = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
//             $pathsDirectory->setWorkspace($workspace);
//             $pathsDirectory->setParent($root);
//             $pathsDirectory->setMimeType("custom/directory");
//             $pathsDirectory->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(7));
        
//             $this->em->persist($pathsDirectory);
//             $this->em->flush();
//         }
        
//         $resourceNode = new ResourceNode();
//         $resourceNode->setClass('Innova\PathBundle\Entity\Path');
//         $resourceNode->setCreator($this->user);
//         $resourceNode->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('path'));
//         $resourceNode->setWorkspace($workspace);
//         $resourceNode->setParent($pathsDirectory);
//         $resourceNode->setMimeType('');
//         $resourceNode->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));
//         $resourceNode->setName('Path');
        
//         $pathName = $this->get('request')->request->get('pathName');
//         $content = $this->get('request')->request->get('path');
        
//         $new_path = new Path;
//         $new_path->setPath($content);
//         $resourceNode->setName($pathName);
//         $new_path->setResourceNode($resourceNode);
        
//         $this->em->persist($resourceNode);
//         $this->em->persist($new_path);
//         $this->em->flush();
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
     * @throws Exception
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
                throw new Exception('You can delete only your own paths.');
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
     */
    public function checkNameIsUnique()
    {
        
    }

    /**
     * Find all paths for a workspace
     */
    public function findAllFromWorkspace($workspace)
    {
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('path');
        $resourceNodes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findByWorkspaceAndResourceType($workspace, $resourceType);
        $paths = array();
        $paths["me"] = array();
        $paths["others"] = array();

        foreach ($resourceNodes as $resourceNode) {
            if ($resourceNode->getCreator() == $this->user){
                $creator = "me";
            }
            else{
                $creator = "others";
            }

            $paths[$creator][] = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($resourceNode);
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
        $path = $this->em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);

        // On récupère la liste des steps avant modification pour supprimer ceux qui ne sont plus utilisés. TO DO : suppression
        $steps = $this->em->getRepository('InnovaPathBundle:Step')->findByPath($path->getId());
        // initialisation array() de steps à ne pas supprimer. Sera rempli dans la function JSONParser
        $stepsToNotDelete = array();
        $excludedResourcesToResourceNodes = array();

        //todo - lister les liens resources2step pour supprimer ceux inutilisés.

        // JSON string to Object - Récupération des childrens de la racine
        $json = json_decode($path->getPath());
        $json_root_steps = $json->steps;

        // Récupération Workspace courant et la resource root
        $workspaceId = $this->request->get('workspaceId');
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        // création du dossier _paths s'il existe pas.
        if (!$pathsDirectory = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneByName("_paths")) {
            $pathsDirectory = new ResourceNode();
            $pathsDirectory->setName("_paths");
            $pathsDirectory->setClass("Claroline\CoreBundle\Entity\Resource\Directory");
            $pathsDirectory->setCreator($this->user);
            $pathsDirectory->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(2));
            $root = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
            $pathsDirectory->setWorkspace($workspace);
            $pathsDirectory->setParent($root);
            $pathsDirectory->setMimeType("custom/directory");
            $pathsDirectory->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(7));

            $this->em->persist($pathsDirectory);
            $this->em->flush();
        }

        // lancement récursion
        $this->JSONParser($json_root_steps, $this->user, $workspace, $pathsDirectory, 0, null, 0, $path, $stepsToNotDelete, $excludedResourcesToResourceNodes);

        // On nettoie la base des steps qui n'ont pas été réutilisé et les step2resourceNode associés
        foreach ($steps as $step) {
           if (!in_array($step->getId(),$stepsToNotDelete)) {
                /*
                $step2ressourceNodes = $this->em->getRepository('InnovaPathBundle:Step2ResourceNode')->findByStep($step->getId());
                foreach ($step2ressourceNodes as $step2ressourceNode) {
                    $this->em->remove($step2ressourceNode);
                }
                */
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
           

            // JSON_STEP UPDATE
            $step->resourceId = $currentStep->getId();

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

            // STEP'S RESOURCES MANAGEMENT
            $currentStep2resourceNodes = $currentStep->getStep2ResourceNodes();
            $step2resourceNodesToNotDelete = array();

            $resourceOrder = 0;
            foreach ($step->resources as $resource) {
                $resourceOrder++;

                // Gestion des ressources non digitales
                if(!$resource->isDigital){
                    if ($resource->resourceId == null){
                        $resourceNode = new ResourceNode();
                        $nonDigitalResource = new NonDigitalResource();
                    }
                    else{
                        $resourceNode = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resource->resourceId);
                        $nonDigitalResource = $this->em->getRepository('InnovaPathBundle:NonDigitalResource')->findOneByResourceNode($resourceNode);
                    }


                    $resourceNode->setClass("Innova\PathBundle\Entity\NonDigitalResource");
                    $resourceNode->setCreator($user);
                    $resourceNode->setResourceType($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("non_digital_resource"));
                    $resourceNode->setWorkspace($workspace);
                    $resourceNode->setParent($pathsDirectory);
                    $resourceNode->setMimeType("");
                    $resourceNode->setName($resource->name);
                    $resourceNode->setIcon($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(3));
                    $this->em->persist($resourceNode);
                    $nonDigitalResource->setNonDigitalResourceType($this->em->getRepository('InnovaPathBundle:NonDigitalResourceType')->findOneByName($resource->subType));
                    $nonDigitalResource->setResourceNode($resourceNode);
                    $nonDigitalResource->setDescription($resource->description);
                    $this->em->persist($nonDigitalResource);
                   
                    $this->em->flush();

                    $resource->resourceId = $resourceNode->getId();
                }

                $excludedResourcesToResourceNodes[$resource->id] = $resource->resourceId;
                $step2ressourceNode = $this->em->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneBy(array(
                                                                'step' => $currentStep, 
                                                                'resourceNode' => $resource->resourceId,
                                                                'excluded' => false
                                                                )
                                                             );
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
                                                                'excluded' => true
                                                                )
                                                            );
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