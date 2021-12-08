<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Tool\ToolSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Not a true Serializer I guess, need to see where it is used. Could be extended after a refactoring.
 */
class OrderedToolTransfer implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ToolSerializer */
    private $toolSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var ContainerInterface */
    private $container;

    public function __construct(
        ObjectManager $om,
        ToolSerializer $toolSerializer,
        RoleSerializer $roleSerializer,
        ContainerInterface $container
    ) {
        $this->om = $om;
        $this->toolSerializer = $toolSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->container = $container;
    }

    public function serialize(OrderedTool $orderedTool, array $options = []): array
    {
        $data = [
            'tool' => $orderedTool->getTool()->getName(),
            'position' => $orderedTool->getOrder(),
            // TODO : should be named rights and there should be a ToolRightsSerializer
            'restrictions' => array_map(function (ToolRights $rights) {
                return [
                    'role' => $this->roleSerializer->serialize($rights->getRole(), [Options::SERIALIZE_MINIMAL]),
                    'mask' => $rights->getMask(),
                ];
            }, $orderedTool->getRights()->toArray()),
        ];

        $serviceName = 'claroline.transfer.'.$orderedTool->getTool()->getName();
        if ($this->container->has($serviceName)) {
            $data['data'] = $this->container->get($serviceName)->serialize($orderedTool->getWorkspace(), $options);
        }

        return $data;
    }

    public function dispatchPreEvent(array $data, array $orderedToolData)
    {
        //use event instead maybe ? or tagged service
        $serviceName = 'claroline.transfer.'.$orderedToolData['tool'];

        if ($this->container->has($serviceName)) {
            $importer = $this->container->get($serviceName);
            if (method_exists($importer, 'setLogger')) {
                $importer->setLogger($this->logger);
            }
            $data = $importer->prepareImport($orderedToolData, $data);
        }

        return $data;
    }

    public function deserialize(array $data, OrderedTool $orderedTool, array $newEntities = [], Workspace $workspace = null, FileBag $bag = null): array
    {
        $createdObjects = [];

        $tool = $this->om->getRepository(Tool::class)->findOneByName($data['tool']);
        if ($tool) {
            $orderedTool->setWorkspace($workspace);
            $orderedTool->setTool($tool);
            $orderedTool->setOrder($data['position']);

            foreach ($data['restrictions'] as $restriction) {
                $role = $this->om->getRepository(Role::class)->findOneBy([
                    'translationKey' => $restriction['role']['translationKey'],
                    'workspace' => $workspace->getId(),
                ]);

                if ($role) {
                    $rights = new ToolRights();
                    $rights->setRole($role);
                    $rights->setMask($restriction['mask']);
                    $rights->setOrderedTool($orderedTool);

                    $this->om->persist($rights);
                }
            }

            //use event instead maybe ? or tagged service
            $serviceName = 'claroline.transfer.'.$orderedTool->getTool()->getName();

            if ($this->container->has($serviceName)) {
                $importer = $this->container->get($serviceName);
                if (isset($data['data'])) {
                    $createdObjects = $importer->deserialize($data['data'], $orderedTool->getWorkspace(), [Options::REFRESH_UUID], $newEntities, $bag);
                }
            }
        }

        return $createdObjects;
    }
}
