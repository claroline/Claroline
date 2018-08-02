<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Finder\Home\WidgetContainerFinder;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.home_tab")
 * @DI\Tag("claroline.serializer")
 *
 * @todo simplify relationships (there are lots of duplicates)
 * @todo simplify serialized structure
 */
class HomeTabSerializer
{
    use SerializerTrait;

    private $serializer;
    private $om;
    private $widgetContainerFinder;

    /**
     * ContactSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer"            = @DI\Inject("claroline.api.serializer"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "widgetContainerFinder" = @DI\Inject("claroline.api.finder.widget_container")
     * })
     *
     * @param SerializerProvider    $serializer
     * @param ObjectManager         $om
     * @param WidgetContainerFinder $widgetContainerFinder
     */
    public function __construct(
        SerializerProvider $serializer,
        ObjectManager $om,
        WidgetContainerFinder $widgetContainerFinder
    ) {
        $this->serializer = $serializer;
        $this->om = $om;
        $this->widgetContainerFinder = $widgetContainerFinder;
    }

    public function getClass()
    {
        return HomeTab::class;
    }

    public function serialize(HomeTab $homeTab, array $options = []): array
    {
        $widgetHomeTabConfigs = $homeTab->getWidgetHomeTabConfigs();
        $containers = [];

        foreach ($widgetHomeTabConfigs as $config) {
            $container = $config->getWidgetInstance()->getContainer();

            if ($container) {
                $containers[$container->getPosition()] = $container;
            }
        }

        $containers = array_values($containers);

        $homeTabConfig = $this->om->getRepository(HomeTabConfig::class)
          ->findOneBy(['homeTab' => $homeTab]);

        $poster = null;
        if ($homeTab->getPoster()) {
            $file = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
                ->findOneBy(['url' => $homeTab->getPoster()]);

            if ($file) {
                $poster = $this->serializer->serialize($file);
            }
        }

        return [
            'id' => $this->getUuid($homeTab, $options),
            'title' => $homeTab->getName(),
            'longTitle' => $homeTab->getLongTitle(),
            'centerTitle' => $homeTab->isCenterTitle(),
            'poster' => $poster,
            'icon' => $homeTab->getIcon(),
            'type' => $homeTab->getType(),
            'position' => $homeTabConfig->getTabOrder(),
            'roles' => array_map(function ($role) {
                return $role->getUuid();
            }, $homeTab->getRoles()),
            'user' => $homeTab->getUser() ? $this->serializer->serialize($homeTab->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
            'workspace' => $homeTab->getWorkspace() ? $this->serializer->serialize($homeTab->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null,
            'widgets' => array_map(function ($container) use ($options) {
                return $this->serializer->serialize($container, $options);
            }, $containers),
        ];
    }

    public function deserialize(array $data, HomeTab $homeTab, array $options = []): HomeTab
    {
        $this->sipe('id', 'setUuid', $data, $homeTab);
        $this->sipe('title', 'setName', $data, $homeTab);
        $this->sipe('longTitle', 'setLongTitle', $data, $homeTab);
        $this->sipe('centerTitle', 'setCenterTitle', $data, $homeTab);
        $this->sipe('poster.url', 'setPoster', $data, $homeTab);
        $this->sipe('icon', 'setIcon', $data, $homeTab);
        $this->sipe('type', 'setType', $data, $homeTab);

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleUuid) {
                $role = $this->om->getRepository(Role::class)
                    ->findOneBy(['uuid' => $roleUuid]);
                $homeTab->addRole($role);
            }

            $existingRoles = $homeTab->getRoles();
            foreach ($existingRoles as $role) {
                if (!in_array($role->getUuid(), $data['roles'])) {
                    // the role no longer exist we can remove it
                    $homeTab->removeRole($role);
                }
            }
        }

        $homeTabConfig = $this->om->getRepository(HomeTabConfig::class)
            ->findOneBy(['homeTab' => $homeTab]);

        if (!$homeTabConfig) {
            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);

            if (isset($data['type'])) {
                $homeTabConfig->setType($data['type']);
            }
        }

        if (isset($data['position'])) {
            $homeTabConfig->setPosition($data['position']);
        }

        $workspace = $user = null;

        if (isset($data['workspace'])) {
            $workspace = $this->serializer->deserialize(Workspace::class, $data['workspace']);
            $homeTab->setWorkspace($workspace);
            $homeTabConfig->setWorkspace($workspace);
        }

        if (isset($data['user'])) {
            $user = $this->serializer->deserialize(User::class, $data['user'], $options);
            $homeTab->setUser($user);
            $homeTabConfig->setUser($user);
        }

        // We either do this or cascade persist ¯\_(ツ)_/¯
        $this->om->persist($homeTabConfig);
        $containerIds = [];

        foreach ($data['widgets'] as $position => $widgetContainer) {
            $widgetContainer = $this->serializer->deserialize(WidgetContainer::class, $widgetContainer, $options);
            $widgetContainer->setPosition($position);
            $this->om->persist($widgetContainer);
            $containerIds[] = $widgetContainer->getUuid();

            //ptet rajouter les instances ici ? je sais pas
            foreach ($widgetContainer->getInstances() as $key => $instance) {
                $widgetHomeTabConfig = new WidgetHomeTabConfig();
                $widgetHomeTabConfig->setUser($user);
                $widgetHomeTabConfig->setWorkspace($workspace);
                $widgetHomeTabConfig->setHomeTab($homeTab);
                $widgetHomeTabConfig->setVisible(true);
                $widgetHomeTabConfig->setLocked(false);
                $widgetHomeTabConfig->setType($homeTab->getType());
                $widgetHomeTabConfig->setWidgetOrder($key);
                $widgetHomeTabConfig->setWidgetInstance($instance);
                $this->om->persist($widgetHomeTabConfig);
            }
        }

        //readytoremove
        $containers = $this->widgetContainerFinder->find(['homeTab' => $homeTab->getUuid()]);

        foreach ($containers as $container) {
            if (!in_array($container->getUuid(), $containerIds)) {
                $this->om->remove($container);
            }
        }

        return $homeTab;
    }
}
