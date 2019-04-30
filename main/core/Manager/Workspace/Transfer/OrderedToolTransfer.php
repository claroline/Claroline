<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\API\Serializer\Tool\ToolSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.transfer.ordered_tool")
 * Not a true Serializer I guess, need to see where it is used. Could be extended after a refactoring
 */
class OrderedToolTransfer
{
    use LoggableTrait;

    /** @var ToolSerializer */
    private $toolSerializer;

    /**
     * @DI\InjectParams({
     *     "toolSerializer" = @DI\Inject("claroline.serializer.tool"),
     *     "roleSerializer" = @DI\Inject("claroline.serializer.role"),
     *     "container"      = @DI\Inject("service_container")
     * })
     *
     * @param ToolSerializer $toolSerializer
     */
    public function __construct(ToolSerializer $toolSerializer, RoleSerializer $roleSerializer, ContainerInterface $container)
    {
        $this->toolSerializer = $toolSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->container = $container;
    }

    public function serialize(OrderedTool $orderedTool, array $options = []): array
    {
        $data = [
          'tool' => $orderedTool->getTool()->getName(),
          'name' => $orderedTool->getName(),
          'position' => $orderedTool->getOrder(),
          'restrictions' => $this->serializeRestrictions($orderedTool, $options),
        ];

        if (in_array(Options::SERIALIZE_TOOL, $options)) {
            $serviceName = 'claroline.transfer.'.$orderedTool->getTool()->getName();

            if ($this->container->has($serviceName)) {
                $data['data'] = $this->container->get($serviceName)->serialize($orderedTool->getWorkspace(), $options);
            }
        }

        return $data;
    }

    private function serializeRestrictions(OrderedTool $orderedTool, array $options = []): array
    {
        $restrictions = [];

        foreach ($orderedTool->getRights() as $right) {
            if (in_array(Options::REFRESH_UUID, $options)) {
                $role = ['translationKey' => $right->getRole()->getTranslationKey(), 'type' => $right->getRole()->getType()];
            } else {
                $role = $this->roleSerializer->serialize($right->getRole(), [Options::SERIALIZE_MINIMAL]);
            }

            $restrictions[] = [
              'role' => $role,
              'mask' => $right->getMask(),
            ];
        }

        return $restrictions;
    }

    public function dispatchPreEvent(array $data, array $orderedToolData)
    {
        //use event instead maybe ? or tagged service
        $serviceName = 'claroline.transfer.'.$orderedToolData['name'];

        if ($this->container->has($serviceName)) {
            $importer = $this->container->get($serviceName);
            if (method_exists($importer, 'setLogger')) {
                $importer->setLogger($this->logger);
            }
            $data = $importer->prepareImport($orderedToolData, $data);
        }

        return $data;
    }

    //only work for creation... other not supported. It's not a true Serializer anyway atm
    public function deserialize(array $data, OrderedTool $orderedTool, array $options = [], Workspace $workspace = null, FileBag $bag = null)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $tool = $om->getRepository(Tool::class)->findOneByName($data['tool']);

        if ($tool) {
            $orderedTool->setWorkspace($workspace);
            $orderedTool->setTool($tool);
            $orderedTool->setName($data['name']);
            $orderedTool->setOrder($data['position']);

            foreach ($data['restrictions'] as $restriction) {
                if (isset($restriction['role']['name'])) {
                    $role = $om->getRepository(Role::class)->findOneBy(['name' => $restriction['role']['name']]);
                } else {
                    $role = $om->getRepository(Role::class)->findOneBy([
                        'translationKey' => $restriction['role']['translationKey'],
                        'workspace' => $workspace->getId(),
                    ]);
                }

                $rights = new ToolRights();
                $rights->setRole($role);
                $rights->setMask($restriction['mask']);
                $rights->setOrderedTool($orderedTool);
                $om->persist($rights);
            }

            //use event instead maybe ? or tagged service
            $serviceName = 'claroline.transfer.'.$orderedTool->getTool()->getName();

            if ($this->container->has($serviceName)) {
                $importer = $this->container->get($serviceName);
                if (method_exists($importer, 'setLogger')) {
                    $importer->setLogger($this->logger);
                }
                if (isset($data['data'])) {
                    $importer->deserialize($data['data'], $orderedTool->getWorkspace(), [Options::REFRESH_UUID], $bag);
                }
            }
        }
    }
}
