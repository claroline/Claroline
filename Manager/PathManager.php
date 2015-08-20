<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Library\Security\Utilities;
use Symfony\Component\Finder\Exception\AccessDeniedException;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\PathBundle\Entity\Path\Path;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Path Manager
 * Manages life cycle of paths
 * @author Innovalangues <contact@innovalangues.net>
 */
class PathManager
{
    /**
     * Current entity manage for data persist
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;

    /**
     * claro resource manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * Security Authorization
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $securityAuth
     */
    protected $securityAuth;

    /**
     * Security Token
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $securityToken
     */
    protected $securityToken;

    /**
     * @var StepManager
     */
    protected $stepManager;

    /**
     * Class constructor - Inject required services
     * @param \Doctrine\Common\Persistence\ObjectManager                                          $objectManager
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface        $securityAuth
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $securityToken
     * @param \Claroline\CoreBundle\Manager\ResourceManager                                       $resourceManager
     * @param \Claroline\CoreBundle\Library\Security\Utilities                                    $utils
     * @param \Innova\PathBundle\Manager\StepManager                                              $stepManager
     */
    public function __construct(
        ObjectManager                 $objectManager,
        AuthorizationCheckerInterface $securityAuth,
        TokenStorageInterface         $securityToken,
        ResourceManager               $resourceManager,
        Utilities                     $utils,
        StepManager                   $stepManager)
    {
        $this->om              = $objectManager;
        $this->securityAuth    = $securityAuth;
        $this->securityToken   = $securityToken;
        $this->resourceManager = $resourceManager;
        $this->utils           = $utils;
        $this->stepManager     = $stepManager;
    }

    /**
     * Check if a user has sufficient rights to execute action on Path
     * @param  string                                           $action
     * @param  \Innova\PathBundle\Entity\Path\Path              $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
     */
    public function checkAccess($action, Path $path, Workspace $workspace = null)
    {
        if (!$this->isAllow($action, $path, $workspace)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Return if a user has sufficient rights to execute action on Path
     * @param  string                                           $actionName
     * @param  \Innova\PathBundle\Entity\Path\Path              $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @return boolean
     */
    public function isAllow($actionName, Path $path, Workspace $workspace = null)
    {
        if ($workspace && $actionName === 'CREATE') {
            $toolRepo = $this->om->getRepository('ClarolineCoreBundle:Role');
            $managerRole = $toolRepo->findManagerRole($workspace);

            return $this->securityAuth->isGranted($managerRole->getName());
        }

        $collection = new ResourceCollection(array ($path->getResourceNode()));

        return $this->securityAuth->isGranted($actionName, $collection);
    }

    /**
     * Get all Paths of the Platform
     * @param bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     */
    public function getPlatformPaths($toPublish = false)
    {
        return $this->om->getRepository('InnovaPathBundle:Path\Path')->findPlatformPaths($toPublish);
    }

    /**
     * Get all Paths of a Workspace
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     */
    public function getWorkspacePaths(Workspace $workspace, $toPublish = false)
    {
        return $this->om->getRepository('InnovaPathBundle:Path\Path')->findWorkspacePaths($workspace, $toPublish);
    }

    /**
     * Find accessible Paths
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @return array
     */
    public function findAccessibleByUser(Workspace $workspace = null)
    {
        $roots = array ();
        if (!empty($workspace)) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $roots[] = $root->getPath();
        }

        $token = $this->securityToken->getToken();
        $userRoles = $this->utils->getRoles($token);

        $entities = $this->om->getRepository('InnovaPathBundle:Path\Path')->findAccessibleByUser($roots, $userRoles);

        // Check edit and delete access for paths
        $paths = array ();
        foreach ($entities as $entity) {
            $paths[] = array (
                'entity'    => $entity,
                'canEdit'   => $this->isAllow('EDIT', $entity),
                'canDelete' => $this->isAllow('DELETE', $entity),
            );
        }

        return $paths;
    }

    /**
     * Get progression of a User into a Path
     * @param \Innova\PathBundle\Entity\Path\Path $path
     * @param \Claroline\CoreBundle\Entity\User $user
     * @return array
     */
    public function getUserProgression(Path $path, User $user = null)
    {
        if (empty($user)) {
            // Get current authenticated User
            $user = $this->securityToken->getToken()->getUser();
        }

        $results = array ();
        if ($user instanceof UserInterface) {
            // We have a logged User => get its progression
            $results = $this->om->getRepository('InnovaPathBundle:UserProgression')->findByPathAndUser($path, $user);
        }

        return $results;
    }

    /**
     * Create a new path
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @return \Innova\PathBundle\Entity\Path\Path
     */
    public function create(Path $path, Workspace $workspace)
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
        $parent =       $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        $resourceType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('innova_path');
        $path = $this->resourceManager->create($path, $resourceType, $this->securityToken->getToken()->getUser(), $workspace, $parent, null);

        return $path;
    }

    /**
     * Edit existing path
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @return \Innova\PathBundle\Entity\Path\Path
     */
    public function edit(Path $path)
    {
        // Check if JSON structure is built
        $structure = $path->getStructure();

        if (empty($structure)) {
            // Initialize path structure
            $path->initializeStructure();
        }

        // Set path as modified (= need publishing to be able to play path with new modifs)
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
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @return boolean
     * @throws \Exception
     */
    public function delete(Path $path)
    {
        // User can delete current path
        $this->om->remove($path->getResourceNode());
        $this->om->flush();

        return $this;
    }

    public function export(Workspace $workspace, array &$files, Path $path)
    {
        $data = array ();

        // Get path data
        $pathData = array ();
        $pathData['description'] = $path->getDescription();
        $pathData['breadcrumbs'] = $path->hasBreadcrumbs();
        $pathData['structure']   = $path->getStructure();

        $data['path'] = $pathData;

        // Process Steps
        $stepsData = array ();
        foreach ($path->getSteps() as $step) {
            $stepsData[] = $this->stepManager->export($step);
        }

        $data['steps'] = $stepsData;

        return $data;
    }

    public function import(array $data, array $resourcesCreated = array ())
    {
        var_dump($data);
        die();

        // Create a new Path object which will be populated with exported data
        $path = new Path();

        // Populate Path properties
        $path->setBreadcrumbs(!empty($data['data']['breadcrumbs']) ? $data['data']['breadcrumbs'] : false);
        $path->setDescription($data['data']['description']);

        /*if (!empty($data['data']['structure'])) {
            $path->setStructure($data['data']['structure']);
        } else {
            $path->initializeStructure();
        }*/

        // Path will be published will import, so it can't be out of sync with JSON structure
        $path->setModified(false);

        // Create steps
        if (!empty($data['data']['structure']) && !empty($data['data']['structure']['steps'])) {
            $createdSteps = array ();
            foreach ($data['data']['structure']['steps'] as $step) {
                $createdSteps = $this->stepManager->import($path, $step, $resourcesCreated, $createdSteps);
            }

            // Inject new structure into path
            // Structure will not be fully updated
            // we have not access to the new Resources IDs because they have not been saved to the DB
            // That's not a problem because when Path is published, the structure is recalculated from the generated Data
            $path->setStructure('{}');
        }

        return $path;
    }
}
