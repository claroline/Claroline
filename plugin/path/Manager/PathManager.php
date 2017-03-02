<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\PathWidgetConfig;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Path Manager
 * Manages life cycle of paths.
 *
 * @author Innovalangues <contact@innovalangues.net>
 */
class PathManager
{
    /**
     * Current entity manage for data persist.
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * claro resource manager.
     *
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * Security Authorization.
     *
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $securityAuth;

    /**
     * Security Token.
     *
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $securityToken;

    /**
     * @var StepManager
     */
    protected $stepManager;

    /**
     * @var PlatformConfigurationHandler
     */
    protected $ch;

    /**
     * Class constructor - Inject required services.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager                                          $objectManager
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface        $securityAuth
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $securityToken
     * @param \Claroline\CoreBundle\Manager\ResourceManager                                       $resourceManager
     * @param \Claroline\CoreBundle\Library\Security\Utilities                                    $utils
     * @param \Innova\PathBundle\Manager\StepManager                                              $stepManager
     * @param PlatformConfigurationHandler                                                        $ch
     */
    public function __construct(
        ObjectManager                 $objectManager,
        AuthorizationCheckerInterface $securityAuth,
        TokenStorageInterface         $securityToken,
        ResourceManager               $resourceManager,
        Utilities                     $utils,
        StepManager                   $stepManager,
        PlatformConfigurationHandler  $ch
    ) {
        $this->om = $objectManager;
        $this->securityAuth = $securityAuth;
        $this->securityToken = $securityToken;
        $this->resourceManager = $resourceManager;
        $this->utils = $utils;
        $this->stepManager = $stepManager;
        $this->ch = $ch;
    }

    /**
     * Check if a user has sufficient rights to execute action on Path.
     *
     * @param string                                           $action
     * @param \Innova\PathBundle\Entity\Path\Path              $path
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function checkAccess($action, Path $path, Workspace $workspace = null)
    {
        if (!$this->isAllow($action, $path, $workspace)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Return if a user has sufficient rights to execute action on Path.
     *
     * @param string                                           $actionName
     * @param \Innova\PathBundle\Entity\Path\Path              $path
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return bool
     */
    public function isAllow($actionName, Path $path, Workspace $workspace = null)
    {
        if ($workspace && $actionName === 'CREATE') {
            $toolRepo = $this->om->getRepository('ClarolineCoreBundle:Role');
            $managerRole = $toolRepo->findManagerRole($workspace);

            return $this->securityAuth->isGranted($managerRole->getName());
        }

        $collection = new ResourceCollection([$path->getResourceNode()]);

        return $this->securityAuth->isGranted($actionName, $collection);
    }

    /**
     * Get widget configuration.
     *
     * @param WidgetInstance $config
     *
     * @return PathWidgetConfig
     */
    public function getWidgetConfig(WidgetInstance $config = null)
    {
        return $this->om->getRepository('InnovaPathBundle:PathWidgetConfig')
            ->findOneBy(['widgetInstance' => $config]);
    }

    /**
     * Get the list of Paths for Widgets.
     *
     * @param PathWidgetConfig $config
     * @param Workspace        $workspace
     *
     * @return array
     */
    public function getWidgetPaths(PathWidgetConfig $config = null, Workspace $workspace = null)
    {
        $roots = [];
        if (!empty($workspace)) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $roots[] = $root->getPath();
        }

        $token = $this->securityToken->getToken();
        $userRoles = $this->utils->getRoles($token);

        $entities = $this->om->getRepository('InnovaPathBundle:Path\Path')->findWidgetPaths($userRoles, $roots, $config);

        // Check edit and delete access for paths
        $paths = [];
        foreach ($entities as $entity) {
            $paths[] = [
                'entity' => $entity,
                'canEdit' => $this->isAllow('EDIT', $entity),
            ];
        }

        return $paths;
    }

    /**
     * Get progression of a User into a Path.
     *
     * @param \Innova\PathBundle\Entity\Path\Path $path
     * @param \Claroline\CoreBundle\Entity\User   $user
     *
     * @return array
     */
    public function getUserProgression(Path $path, User $user = null)
    {
        if (empty($user)) {
            // Get current authenticated User
            $user = $this->securityToken->getToken()->getUser();
        }

        $results = [];
        if ($user instanceof UserInterface) {
            // We have a logged User => get its progression
            $results = $this->om->getRepository('InnovaPathBundle:UserProgression')->findByPathAndUser($path, $user);
        }

        return $results;
    }

    /**
     * Create a new path.
     *
     * @param \Innova\PathBundle\Entity\Path\Path              $path
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
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
        $parent = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        $resourceType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('innova_path');
        $path = $this->resourceManager->create($path, $resourceType, $this->securityToken->getToken()->getUser(), $workspace, $parent, null);

        return $path;
    }

    /**
     * Edit existing path.
     *
     * @param \Innova\PathBundle\Entity\Path\Path $path
     *
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
     * Delete path.
     *
     * @param \Innova\PathBundle\Entity\Path\Path $path
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function delete(Path $path)
    {
        // User can delete current path
        $this->om->remove($path->getResourceNode());
        $this->om->flush();

        return $this;
    }

    public function export($workspace, array &$files, Path $path)
    {
        $data = [];

        // Get path data
        $pathData = [];
        $pathData['description'] = $path->getDescription();
        $pathData['breadcrumbs'] = $path->hasBreadcrumbs();
        $pathData['modified'] = $path->isModified();
        $pathData['published'] = $path->isPublished();
        $pathData['summaryDisplayed'] = $path->isSummaryDisplayed();
        $pathData['completeBlockingCondition'] = $path->isCompleteBlockingCondition();
        $pathData['manualProgressionAllowed'] = $path->isManualProgressionAllowed();

        // Get path structure into a file (to replace resources ID with placeholders)
        $uid = uniqid().'.txt';
        $tmpPath = $this->ch->getParameter('tmp_dir').DIRECTORY_SEPARATOR.$uid;
        $structure = $path->getStructure();
        file_put_contents($tmpPath, $structure);
        $files[$uid] = $tmpPath;

        $pathData['structure'] = $uid;

        $data['path'] = $pathData;

        // Process Steps
        $data['steps'] = [];
        if ($path->isPublished()) {
            $stepsData = [];
            foreach ($path->getSteps() as $step) {
                $stepsData[] = $this->stepManager->export($step);
            }

            $data['steps'] = $stepsData;
        }

        return $data;
    }

    /**
     * Import a Path into the Platform.
     *
     * @param string $structure
     * @param array  $data
     * @param array  $resourcesCreated
     *
     * @return Path
     */
    public function import($structure, array $data, array $resourcesCreated = [])
    {
        // Create a new Path object which will be populated with exported data
        $path = new Path();

        $pathData = $data['data']['path'];

        // Populate Path properties
        $path->setBreadcrumbs(!empty($pathData['breadcrumbs']) ? $pathData['breadcrumbs'] : false);
        $path->setDescription($pathData['description']);
        $path->setModified($pathData['modified']);
        $path->setSummaryDisplayed($pathData['summaryDisplayed']);
        $path->setCompleteBlockingCondition($pathData['completeBlockingCondition']);
        $path->setManualProgressionAllowed($pathData['manualProgressionAllowed']);

        // Create steps
        $stepData = $data['data']['steps'];
        if (!empty($stepData)) {
            $createdSteps = [];
            foreach ($stepData as $step) {
                $createdSteps = $this->stepManager->import($path, $step, $resourcesCreated, $createdSteps);
            }
        }

        // Inject empty structure into path (will be replaced by a version with updated IDs later in the import process)
        $path->setStructure($structure);

        return $path;
    }

    /**
     * Get list of users who have called for unlock.
     *
     * @param Path $path
     *
     * @return mixed
     */
    public function getPathLockedProgression(Path $path)
    {
        $results = $this->om->getRepository('InnovaPathBundle:UserProgression')
            ->findByPathAndLockedStep($path);

        return $results;
    }

    /**
     * Get all Paths of the Platform.
     *
     * @param bool $toPublish If false, returns all paths, if true returns only paths which need publishing
     */
    public function getPlatformPaths($toPublish = false)
    {
        return $this->om->getRepository('InnovaPathBundle:Path\Path')->findPlatformPaths($toPublish);
    }

    /**
     * Get all Paths of a Workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param bool                                             $toPublish If false, returns all paths, if true returns only paths which need publishing
     */
    public function getWorkspacePaths(Workspace $workspace, $toPublish = false)
    {
        return $this->om->getRepository('InnovaPathBundle:Path\Path')->findWorkspacePaths($workspace, $toPublish);
    }

    /**
     * Find accessible Paths.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function findAccessibleByUser(Workspace $workspace = null)
    {
        $roots = [];
        if (!empty($workspace)) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $roots[] = $root->getPath();
        }

        $token = $this->securityToken->getToken();
        $userRoles = $this->utils->getRoles($token);

        $entities = $this->om->getRepository('InnovaPathBundle:Path\Path')->findAccessibleByUser($roots, $userRoles);

        // Check edit and delete access for paths
        $paths = [];
        foreach ($entities as $entity) {
            $paths[] = [
                'entity' => $entity,
                'canEdit' => $this->isAllow('EDIT', $entity),
                'canDelete' => $this->isAllow('DELETE', $entity),
            ];
        }

        return $paths;
    }
}
