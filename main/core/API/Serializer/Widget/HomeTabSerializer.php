<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Finder\Home\WidgetContainerFinder;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * @todo simplify relationships (there are lots of duplicates)
 * @todo simplify serialized structure
 */
class HomeTabSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var WidgetContainerFinder */
    private $widgetContainerFinder;
    /** @var WidgetContainerSerializer */
    private $widgetContainerSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var PublicFileSerializer */
    private $publicFileSerializer;

    /**
     * HomeTabSerializer constructor.
     *
     * @param ObjectManager             $om
     * @param WidgetContainerFinder     $widgetContainerFinder
     * @param WidgetContainerSerializer $widgetContainerSerializer
     * @param WorkspaceSerializer       $workspaceSerializer
     * @param UserSerializer            $userSerializer
     * @param RoleSerializer            $roleSerializer
     * @param PublicFileSerializer      $publicFileSerializer
     */
    public function __construct(
        ObjectManager $om,
        WidgetContainerFinder $widgetContainerFinder,
        WidgetContainerSerializer $widgetContainerSerializer,
        WorkspaceSerializer $workspaceSerializer,
        UserSerializer $userSerializer,
        RoleSerializer $roleSerializer,
        PublicFileSerializer $publicFileSerializer
    ) {
        $this->om = $om;
        $this->widgetContainerFinder = $widgetContainerFinder;
        $this->widgetContainerSerializer = $widgetContainerSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->userSerializer = $userSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->publicFileSerializer = $publicFileSerializer;
    }

    public function getName()
    {
        return 'home_tab';
    }

    public function getClass()
    {
        return HomeTab::class;
    }

    public function serialize(HomeTab $homeTab, array $options = []): array
    {
        $homeTabConfig = $this->getConfig($homeTab);

        if (!$homeTabConfig) {
            //something went wrong
            return [];
        }

        /** @var WidgetContainer[] $savedContainers */
        $savedContainers = $homeTab->getWidgetContainers()->toArray();
        $containers = [];

        foreach ($savedContainers as $container) {
            //temporary
            $widgetContainerConfig = $container->getWidgetContainerConfigs()[0];
            if ($widgetContainerConfig) {
                if (!array_key_exists($widgetContainerConfig->getPosition(), $containers)) {
                    $containers[$widgetContainerConfig->getPosition()] = $container;
                } else {
                    $containers[] = $container;
                }
            }
        }

        ksort($containers);
        $containers = array_values($containers);

        $poster = null;

        if ($homeTab->getPoster()) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $homeTab->getPoster()]);

            if ($file) {
                $poster = $this->publicFileSerializer->serialize($file);
            }
        }

        $data = [
            'id' => $homeTab->getUuid(),
            'title' => $homeTabConfig->getName(),
            'slug' => $homeTabConfig->getLongTitle() ? substr(strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $homeTabConfig->getLongTitle()))), 0, 128) : 'new',
            'longTitle' => $homeTabConfig->getLongTitle(),
            'centerTitle' => $homeTabConfig->isCenterTitle(),
            'poster' => $poster,
            'icon' => $homeTabConfig->getIcon(),
            'type' => $homeTab->getType(),
            'position' => $homeTabConfig->getTabOrder(),
            'restrictions' => [
                'hidden' => !$homeTabConfig->isVisible(),
                'roles' => array_map(function (Role $role) {
                    return $this->roleSerializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $homeTabConfig->getRoles()->toArray()),
            ],
            'display' => [
                'color' => $homeTabConfig->getColor(),
            ],
            'user' => $homeTab->getUser() ? $this->userSerializer->serialize($homeTab->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
            'widgets' => array_map(function ($container) use ($options) {
                return $this->widgetContainerSerializer->serialize($container, $options);
            }, $containers),
        ];

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $data['workspace'] = $homeTab->getWorkspace() ? $this->workspaceSerializer->serialize($homeTab->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null;
        }

        return $data;
    }

    public function deserialize(array $data, HomeTab $homeTab, array $options = []): HomeTab
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $homeTab);
        } else {
            $homeTab->refreshUuid();
        }

        $homeTabConfig = $this->om->getRepository(HomeTabConfig::class)
          ->findOneBy(['homeTab' => $homeTab]);

        if (!$homeTabConfig) {
            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
        }

        $this->sipe('title', 'setName', $data, $homeTabConfig);
        $this->sipe('position', 'setPosition', $data, $homeTabConfig);
        $this->sipe('longTitle', 'setLongTitle', $data, $homeTabConfig);
        $this->sipe('centerTitle', 'setCenterTitle', $data, $homeTabConfig);
        $this->sipe('poster.url', 'setPoster', $data, $homeTab);
        $this->sipe('icon', 'setIcon', $data, $homeTabConfig);
        $this->sipe('type', 'setType', $data, $homeTab);
        $this->sipe('display.color', 'setColor', $data, $homeTabConfig);

        if (isset($data['restrictions'])) {
            if (isset($data['restrictions']['hidden'])) {
                $homeTabConfig->setVisible(!$data['restrictions']['hidden']);
            }

            if (isset($data['restrictions']['roles'])) {
                $existingRoles = $homeTabConfig->getRoles()->toArray();

                foreach ($data['restrictions']['roles'] as $roleData) {
                    /** @var Role $role */
                    $role = $this->om->getRepository(Role::class)->findOneBy(['uuid' => $roleData['id']]);

                    $homeTabConfig->addRole($role);
                }

                $roles = array_map(function (array $role) {
                    return $role['id'];
                }, $data['restrictions']['roles']);

                foreach ($existingRoles as $role) {
                    if (!in_array($role->getUuid(), $roles)) {
                        // the role no longer exist we can remove it
                        $homeTabConfig->removeRole($role);
                    }
                }
            }
        }

        if (isset($data['workspace'])) {
            $workspace = $this->om->getObject($data['workspace'], Workspace::class);
            $homeTab->setWorkspace($workspace);
        }

        if (isset($data['user'])) {
            $user = $this->om->getObject($data['user'], User::class);
            $homeTab->setUser($user);
        }

        if (isset($data['widgets'])) {
            /** @var WidgetContainer[] $currentContainers */
            $currentContainers = $homeTab->getWidgetContainers()->toArray();
            $containerIds = [];

            // update containers
            foreach ($data['widgets'] as $position => $widgetContainerData) {
                if (isset($widgetContainerData['id'])) {
                    $widgetContainer = $homeTab->getWidgetContainer($widgetContainerData['id']);
                }

                if (empty($widgetContainer)) {
                    $widgetContainer = new WidgetContainer();
                }

                $this->widgetContainerSerializer->deserialize($widgetContainerData, $widgetContainer, $options);
                $widgetContainer->setHomeTab($homeTab);
                $widgetContainerConfig = $widgetContainer->getWidgetContainerConfigs()[0];
                $widgetContainerConfig->setPosition($position);
                $containerIds[] = $widgetContainer->getUuid();
            }

            // removes containers which no longer exists
            foreach ($currentContainers as $currentContainer) {
                if (!in_array($currentContainer->getUuid(), $containerIds)) {
                    $currentContainer->setHomeTab(null);
                    $this->om->remove($currentContainer);
                }
            }
        }

        return $homeTab;
    }

    /**
     * @param HomeTab $tab
     *
     * @return HomeTabConfig
     */
    public function getConfig(HomeTab $tab)
    {
        /** @var HomeTabConfig $homeTabConfig */
        $homeTabConfig = $this->om->getRepository(HomeTabConfig::class)
          ->findOneBy(['homeTab' => $tab]);

        return $homeTabConfig;
    }
}
