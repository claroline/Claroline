<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manages life cycle of paths.
 *
 * @DI\Service("innova_path.manager.path")
 */
class PathManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var StepManager
     */
    private $stepManager;

    /**
     * @var PlatformConfigurationHandler
     */
    private $platformConfig;

    /**
     * PathManager constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "authorization"  = @DI\Inject("security.authorization_checker"),
     *     "stepManager"    = @DI\Inject("innova_path.manager.step"),
     *     "platformConfig" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param ObjectManager                 $om
     * @param AuthorizationCheckerInterface $authorization
     * @param StepManager                   $stepManager
     * @param PlatformConfigurationHandler  $platformConfig
     */
    public function __construct(
        ObjectManager                 $om,
        AuthorizationCheckerInterface $authorization,
        StepManager                   $stepManager,
        PlatformConfigurationHandler  $platformConfig)
    {
        $this->om = $om;
        $this->authorization = $authorization;
        $this->stepManager = $stepManager;
        $this->platformConfig = $platformConfig;
    }

    public function canEdit(Path $path)
    {
        $collection = new ResourceCollection([$path->getResourceNode()]);

        return $this->authorization->isGranted('ADMINISTRATE', $collection);
    }

    /**
     * Edit existing path.
     *
     * @param Path $path
     *
     * @return Path
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
     * @param Path $path
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
    }

    public function export(Path $path, array &$files)
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
        $tmpPath = $this->platformConfig->getParameter('tmp_dir').DIRECTORY_SEPARATOR.$uid;
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
}
