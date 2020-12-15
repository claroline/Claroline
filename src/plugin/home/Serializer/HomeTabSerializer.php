<?php

namespace Claroline\HomeBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Entity\HomeTabConfig;
use Claroline\HomeBundle\Entity\Type\AbstractTab;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @todo simplify relationships (there are lots of duplicates)
 * @todo simplify serialized structure
 */
class HomeTabSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var PublicFileSerializer */
    private $publicFileSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        WorkspaceSerializer $workspaceSerializer,
        UserSerializer $userSerializer,
        RoleSerializer $roleSerializer,
        PublicFileSerializer $publicFileSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->serializer = $serializer;
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
            'poster' => $poster,
            'icon' => $homeTabConfig->getIcon(),
            'context' => $homeTab->getContext(),
            'type' => $homeTab->getType(),
            'class' => $homeTab->getClass(),
            'position' => $homeTab->getOrder(),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $homeTab),
                'edit' => $this->authorization->isGranted('EDIT', $homeTab),
                'delete' => $this->authorization->isGranted('DELETE', $homeTab),
            ],
            'restrictions' => [
                'hidden' => !$homeTabConfig->isVisible(),
                'roles' => array_map(function (Role $role) {
                    return $this->roleSerializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $homeTabConfig->getRoles()->toArray()),
            ],
            'display' => [
                'color' => $homeTabConfig->getColor(),
                'centerTitle' => $homeTabConfig->isCenterTitle(),
            ],
            'user' => $homeTab->getUser() ? $this->userSerializer->serialize($homeTab->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
            'children' => array_map(function (HomeTab $child) use ($options) {
                return $this->serialize($child, $options);
            }, $homeTab->getChildren()->toArray()),
        ];

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $data['workspace'] = $homeTab->getWorkspace() ? $this->workspaceSerializer->serialize($homeTab->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null;
        }

        // retrieves the custom configuration of the widget if any
        if ($homeTab->getClass()) {
            // loads configuration entity for the current instance
            $typeParameters = $this->om
                ->getRepository($homeTab->getClass())
                ->findOneBy(['tab' => $homeTab]);

            $parameters = [];
            if ($typeParameters && $this->serializer->has($typeParameters)) {
                // serializes custom configuration
                $parameters = $this->serializer->serialize($typeParameters, $options);
            }

            if (!empty($parameters)) {
                $data['parameters'] = $parameters;
            }
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

        $homeTabConfig = $this->getConfig($homeTab);
        if (!$homeTabConfig) {
            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
        }

        $this->sipe('title', 'setName', $data, $homeTabConfig);
        $this->sipe('position', 'setOrder', $data, $homeTab);
        $this->sipe('longTitle', 'setLongTitle', $data, $homeTabConfig);
        $this->sipe('poster.url', 'setPoster', $data, $homeTab);
        $this->sipe('icon', 'setIcon', $data, $homeTabConfig);
        $this->sipe('context', 'setContext', $data, $homeTab);
        $this->sipe('type', 'setType', $data, $homeTab);
        $this->sipe('class', 'setClass', $data, $homeTab);
        $this->sipe('display.color', 'setColor', $data, $homeTabConfig);
        $this->sipe('display.centerTitle', 'setCenterTitle', $data, $homeTabConfig);

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
            /** @var Workspace $workspace */
            $workspace = $this->om->getObject($data['workspace'], Workspace::class);
            $homeTab->setWorkspace($workspace);
        }

        if (isset($data['user'])) {
            /** @var User $user */
            $user = $this->om->getObject($data['user'], User::class);
            $homeTab->setUser($user);
        }

        // process custom configuration of the tab if any
        if ($homeTab->getClass()) {
            $parametersClass = $homeTab->getClass();

            // loads configuration entity for the current instance
            $typeParameters = $this->om
                ->getRepository($parametersClass)
                ->findOneBy(['tab' => $homeTab]);

            if (!$typeParameters || in_array(Options::REFRESH_UUID, $options)) {
                // no existing parameters => initializes one

                /** @var AbstractTab $typeParameters */
                $typeParameters = new $parametersClass();
            }

            // deserializes custom config and link it to the instance
            if (isset($data['parameters']) && $this->serializer->has($typeParameters)) {
                $typeParameters = $this->serializer->deserialize($data['parameters'], $typeParameters, $options);
            }
            $typeParameters->setTab($homeTab);
            $this->om->persist($typeParameters);
        }

        // Set children steps
        if (isset($data['children'])) {
            /** @var HomeTab[] $currentChildren */
            $currentChildren = $homeTab->getChildren()->toArray();
            $ids = [];

            // updates tabs
            foreach ($data['children'] as $childIndex => $childData) {
                $child = null;
                if ($childData['id']) {
                    $child = $this->om->getRepository(HomeTab::class)->findOneBy(['uuid' => $childData['id']]);
                    // TODO manage tabs moving
                    /*foreach ($currentChildren as $currentChild) {
                        if ($currentChild->getUuid() === $childData['id']) {
                            $child = $currentChild;
                            break;
                        }
                    }*/
                }

                if (empty($child)) {
                    $child = new HomeTab();
                    //$this->om->persist($child);
                }

                $child->setOrder($childIndex);
                $homeTab->addChild($child);

                $this->deserialize($childData, $child, $options);
                $ids[] = $child->getUuid();
            }

            // removes tabs which no longer exists
            foreach ($currentChildren as $currentTab) {
                if (!in_array($currentTab->getUuid(), $ids)) {
                    //$this->om->remove($currentTab);
                    $homeTab->removeChild($currentTab);
                }
            }
        }

        return $homeTab;
    }

    private function getConfig(HomeTab $tab): ?HomeTabConfig
    {
        /** @var HomeTabConfig $homeTabConfig */
        $homeTabConfig = $this->om->getRepository(HomeTabConfig::class)
            ->findOneBy(['homeTab' => $tab]);

        return $homeTabConfig;
    }
}
