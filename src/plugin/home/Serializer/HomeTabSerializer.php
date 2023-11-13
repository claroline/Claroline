<?php

namespace Claroline\HomeBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Entity\Type\AbstractTab;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HomeTabSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly WorkspaceSerializer $workspaceSerializer,
        private readonly UserSerializer $userSerializer,
        private readonly RoleSerializer $roleSerializer
    ) {
    }

    public function getName(): string
    {
        return 'home_tab';
    }

    public function getClass(): string
    {
        return HomeTab::class;
    }

    public function serialize(HomeTab $homeTab, array $options = []): array
    {
        $data = [
            'id' => $homeTab->getUuid(),
            'title' => $homeTab->getName(),
            'slug' => $homeTab->getLongTitle() ? substr(strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $homeTab->getLongTitle()))), 0, 128) : 'new',
            'longTitle' => $homeTab->getLongTitle(),
            'poster' => $homeTab->getPoster(),
            'icon' => $homeTab->getIcon(),
            'type' => $homeTab->getType(),
            'class' => $homeTab->getClass(), // TODO : should no longer be exposed here
            'position' => $homeTab->getOrder(),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $homeTab),
                'edit' => $this->authorization->isGranted('EDIT', $homeTab),
                'delete' => $this->authorization->isGranted('DELETE', $homeTab),
            ],
            'restrictions' => [
                'hidden' => $homeTab->isHidden(),
                'dates' => DateRangeNormalizer::normalize(
                    $homeTab->getAccessibleFrom(),
                    $homeTab->getAccessibleUntil()
                ),
                'code' => $homeTab->getAccessCode(),
                'roles' => array_map(function (Role $role) {
                    return $this->roleSerializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $homeTab->getRoles()->toArray()),
            ],
            'display' => [
                'color' => $homeTab->getColor(),
                'centerTitle' => $homeTab->isCenterTitle(),
                'showTitle' => $homeTab->getShowTitle(),
            ],

            // TODO : should no longer be exposed here (still required by update and ws import)
            'children' => array_map(function (HomeTab $child) use ($options) {
                return $this->serialize($child, $options);
            }, $homeTab->getChildren()->toArray()),
        ];

        // retrieves the custom configuration of the tab if any
        if (!in_array(Options::SERIALIZE_MINIMAL, $options) && $homeTab->getClass()) {
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

        $this->sipe('title', 'setName', $data, $homeTab);
        $this->sipe('position', 'setOrder', $data, $homeTab);
        $this->sipe('longTitle', 'setLongTitle', $data, $homeTab);
        $this->sipe('poster', 'setPoster', $data, $homeTab);
        $this->sipe('icon', 'setIcon', $data, $homeTab);
        $this->sipe('type', 'setType', $data, $homeTab);
        $this->sipe('class', 'setClass', $data, $homeTab);
        $this->sipe('display.color', 'setColor', $data, $homeTab);
        $this->sipe('display.centerTitle', 'setCenterTitle', $data, $homeTab);
        $this->sipe('display.showTitle', 'setShowTitle', $data, $homeTab);

        if (isset($data['restrictions'])) {
            $this->sipe('restrictions.code', 'setAccessCode', $data, $homeTab);
            $this->sipe('restrictions.hidden', 'setHidden', $data, $homeTab);

            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $homeTab->setAccessibleFrom($dateRange[0]);
                $homeTab->setAccessibleUntil($dateRange[1]);
            }

            if (isset($data['restrictions']['roles'])) {
                $existingRoles = $homeTab->getRoles()->toArray();

                foreach ($data['restrictions']['roles'] as $roleData) {
                    /** @var Role $role */
                    $role = $this->om->getRepository(Role::class)->findOneBy(['uuid' => $roleData['id']]);
                    if ($role) {
                        $homeTab->addRole($role);
                    }
                }

                $roles = array_map(function (array $role) {
                    return $role['id'];
                }, $data['restrictions']['roles']);

                foreach ($existingRoles as $role) {
                    if (!in_array($role->getUuid(), $roles)) {
                        // the role no longer exist we can remove it
                        $homeTab->removeRole($role);
                    }
                }
            }
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

        // Set children tabs
        // TODO : should no longer be exposed here (still required by update and ws import)
        if (isset($data['children'])) {
            /** @var HomeTab[] $currentChildren */
            $currentChildren = $homeTab->getChildren()->toArray();
            $ids = [];

            // updates tabs
            foreach ($data['children'] as $childIndex => $childData) {
                $child = null;
                if ($childData['id'] && !in_array(Options::REFRESH_UUID, $options)) {
                    $child = $this->om->getRepository(HomeTab::class)->findOneBy(['uuid' => $childData['id']]);
                }

                if (empty($child)) {
                    $child = new HomeTab();
                }

                $child->setOrder($childIndex);
                $child->setContextName($homeTab->getContextName());
                $child->setContextId($homeTab->getContextId());
                $homeTab->addChild($child);

                $child = $this->deserialize($childData, $child, $options);
                $ids[] = $child->getUuid();
            }

            // removes tabs which no longer exists
            foreach ($currentChildren as $currentTab) {
                if (!in_array($currentTab->getUuid(), $ids)) {
                    $homeTab->removeChild($currentTab);
                }
            }
        }

        return $homeTab;
    }
}
