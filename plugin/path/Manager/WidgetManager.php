<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\PathWidgetConfig;
use Innova\PathBundle\Repository\PathRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Manages path widgets.
 *
 * @DI\Service("innova_path.manager.widget")
 */
class WidgetManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ClaroUtilities
     */
    private $utils;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * @var PathManager
     */
    private $pathManager;

    /**
     * @var UserProgressionManager
     */
    private $userProgressionManager;

    /**
     * @var PathRepository
     */
    private $pathRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $configRepository;

    /**
     * WidgetManager constructor.
     *
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "utils"                  = @DI\Inject("claroline.security.utilities"),
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager"),
     *     "pathManager"            = @DI\Inject("innova_path.manager.path"),
     *     "userProgressionManager" = @DI\Inject("innova_path.manager.user_progression")
     * })
     *
     * @param ObjectManager          $om
     * @param TokenStorageInterface  $tokenStorage
     * @param Utilities              $utils
     * @param ResourceManager        $resourceManager
     * @param PathManager            $pathManager
     * @param UserProgressionManager $userProgressionManager
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        Utilities $utils,
        ResourceManager $resourceManager,
        PathManager $pathManager,
        UserProgressionManager $userProgressionManager
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->utils = $utils;
        $this->resourceManager = $resourceManager;
        $this->pathManager = $pathManager;
        $this->userProgressionManager = $userProgressionManager;

        $this->pathRepository = $this->om->getRepository('InnovaPathBundle:Path\Path');
        $this->configRepository = $this->om->getRepository('InnovaPathBundle:PathWidgetConfig');
    }

    /**
     * Get widget configuration.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @return object|PathWidgetConfig
     */
    public function getConfig(WidgetInstance $widgetInstance)
    {
        return $this->configRepository->findOneBy([
            'widgetInstance' => $widgetInstance,
        ]);
    }

    /**
     * Get the list of Paths for Widgets.
     *
     * @param WidgetInstance $widgetInstance
     * @param bool           $addProgression
     *
     * @return array
     */
    public function getPaths(WidgetInstance $widgetInstance, $addProgression = false)
    {
        $workspace = $widgetInstance->getWorkspace();
        $roots = [];

        if (!empty($workspace)) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $roots[] = $root->getPath();
        }

        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $userRoles = $this->utils->getRoles($token);

        $paths = $this->pathRepository->findWidgetPaths($userRoles, $roots, $this->getConfig($widgetInstance));

        return array_map(function (Path $path) use ($user, $addProgression) {
            return [
                'entity' => $path,
                'canEdit' => $this->pathManager->canEdit($path),
                'progression' => $addProgression ? $this->userProgressionManager->calculateUserProgression($user, [$path]) : null,
            ];
        }, $paths);
    }
}
