<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/19/16
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Utilities\PaginatedCollectionRepresentation;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\PortalRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PortalManager.
 *
 * @DI\Service("claroline.manager.portal_manager")
 */
class PortalManager
{
    private $defaultResourceTypes = [
        'workspace', 'icap_lesson', 'icap_wiki', 'icap_website',
        'icap_blog', 'file', 'text', 'claroline_forum', 'innova_path',
    ];

    private $searchTypesOrder = [
        'workspace', 'innova_path', 'file', 'icap_blog', 'icap_wiki',
        'claroline_forum',
    ];

    private $maxResultsPerPage = 50;

    private $visibleResourceTypes = 6;

    /** @var ObjectManager  */
    private $om;
    /** @var TranslatorInterface  */
    private $translator;
    /** @var PlatformConfigurationHandler  */
    private $configHandler;
    /** @var PortalRepository  */
    private $portalRepo;
    /** @var PluginManager  */
    private $pluginManager;

    /**
     * PortalManager constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator" = @DI\Inject("translator"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "portalRepo" = @DI\Inject("claroline.repository.portal"),
     *     "pluginManager" = @DI\Inject("claroline.manager.plugin_manager")
     * })
     *
     * @param ObjectManager                $om
     * @param TranslatorInterface          $translator
     * @param PlatformConfigurationHandler $configHandler
     * @param PortalRepository             $portalRepo
     * @param PluginManager                $pluginManager
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $configHandler,
        PortalRepository $portalRepo,
        PluginManager $pluginManager
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->configHandler = $configHandler;
        $this->portalRepo = $portalRepo;
        $this->pluginManager = $pluginManager;
    }

    public function getAllResourceTypesAsChoices()
    {
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAllTypeNames();
        $resourceTypes[] = array('name' => 'workspace');
        $excludeTypes = array('directory');

        return $this->sortAlphabeticallyResourceTypesForChoices($resourceTypes, $excludeTypes);
    }

    public function getPortalEnabledResourceTypes()
    {
        $resourceTypes = $this->configHandler->getParameter('portal_enabled_resources');
        if (!isset($resourceTypes)) {
            $resourceTypes = $this->defaultResourceTypes;
        }

        return $resourceTypes;
    }

    public function getPortalEnabledResourceTypesForSearch()
    {
        $resourceTypes = $this->getPortalEnabledResourceTypes();
        if (empty($resourceTypes)) {
            return array();
        }
        $sortedTypes = array('all');
        foreach ($this->searchTypesOrder as $type) {
            if (($idx = array_search($type, $resourceTypes)) !== false) {
                unset($resourceTypes[$idx]);
                $sortedTypes[] = $type;
            }
        }
        sort($resourceTypes);

        $resourceTypes = array_merge($sortedTypes, $resourceTypes);
        $more = array();
        if (count($resourceTypes) > $this->visibleResourceTypes) {
            $more = array_slice($resourceTypes, $this->visibleResourceTypes - 1);
            $resourceTypes = array_slice($resourceTypes, 0, $this->visibleResourceTypes - 1);
        }

        return array('visible' => $resourceTypes, 'more' => $more);
    }

    public function setPortalEnabledResourceTypes($resourceTypes)
    {
        $this->configHandler->setParameter('portal_enabled_resources', $resourceTypes);
    }

    public function getLastPublishedResourcesForEnabledTypes($limit = 5)
    {
        $resources = $this->portalRepo->findLastResourcesForTypes($this->getPortalEnabledResourceTypes(), $limit);
        $resultResources = array();
        $previousType = null;
        $key = null;
        $images = isset($resources['image']) ? $resources['image'] : array();
        foreach ($resources['resources'] as $resource) {
            $type = $resource['resourceType'];
            if ($type != $previousType || $key === null) {
                $key = $this->translator->trans($resource['resourceType'], array(), 'resource');
                if (!isset($resultResources[$key])) {
                    $resultResources[$key] = array('type' => $type, 'list' => array());
                }
            }
            array_push($resultResources[$key]['list'], $resource);
        }
        unset($resources['resources']);
        unset($resources['image']);
        foreach ($resources as $type => $items) {
            if (!empty($items)) {
                $key = $this->translator->trans($type, array(), 'resource');
                $resultResources[$key] = array('type' => $type, 'list' => $resources[$type]);
            }
        }
        ksort($resultResources);

        return array('lastResources' => $resultResources, 'images' => $images);
    }

    public function searchResourcesByType($query, $page = 1, $resourceType = null)
    {
        if ($resourceType === null || $resourceType == 'all') {
            $resourceTypes = $this->getPortalEnabledResourceTypes();
        } else {
            $resourceTypes = array($resourceType);
        }
        $isTagEnabled = $this->pluginManager->isLoaded('ClarolineTagBundle');
        $totalItems = $this
            ->portalRepo
            ->countSearchResultsByResourceTypes($query, $resourceTypes, $isTagEnabled);
        $pageResults = $this
            ->portalRepo
            ->searchResourcesByResourceTypes($query, $resourceTypes, $isTagEnabled, $page, $this->maxResultsPerPage);
        $pagerfantaRepresentation = new PaginatedCollectionRepresentation();
        $paginatedCollection = $pagerfantaRepresentation->createRepresentationFromValues(
            $pageResults,
            $totalItems,
            $this->maxResultsPerPage,
            $page
        );

        return $paginatedCollection;
    }

    private function sortAlphabeticallyResourceTypesForChoices($resourceTypes, $excludeTypes)
    {
        //Sort choices alphabetically
        $choices = array();
        foreach ($resourceTypes as $type) {
            if (!in_array($type['name'], $excludeTypes)) {
                $key = $this->translator->trans($type['name'], array(), 'resource');
                $choices[$key] = $type['name'];
            }
        }
        ksort($choices);

        return $choices;
    }
}
