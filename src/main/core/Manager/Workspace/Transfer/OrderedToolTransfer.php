<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Tool\ToolSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderedToolTransfer implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ToolSerializer */
    private $toolSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ContainerInterface */
    private $container;

    public function __construct(
        ObjectManager $om,
        ToolSerializer $toolSerializer,
        RoleSerializer $roleSerializer,
        StrictDispatcher $dispatcher,
        ContainerInterface $container
    ) {
        $this->om = $om;
        $this->toolSerializer = $toolSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
    }

    public function serialize(OrderedTool $orderedTool, FileBag $fileBag): array
    {
        // get custom tool data
        /** @var ExportToolEvent $event */
        $event = $this->dispatcher->dispatch(ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, $orderedTool->getTool()->getName()), ExportToolEvent::class, [
            $orderedTool->getTool()->getName(), AbstractTool::WORKSPACE, $orderedTool->getWorkspace(), $fileBag,
        ]);

        return [
            'tool' => $orderedTool->getTool()->getName(),
            'position' => $orderedTool->getOrder(),
            // TODO : should be named rights and there should be a ToolRightsSerializer
            'restrictions' => array_map(function (ToolRights $rights) {
                return [
                    'role' => $this->roleSerializer->serialize($rights->getRole(), [Options::SERIALIZE_MINIMAL]),
                    'mask' => $rights->getMask(),
                ];
            }, $orderedTool->getRights()->toArray()),
            'data' => $event->getData(),
        ];
    }

    public function deserialize(array $data, OrderedTool $orderedTool, array $newEntities = [], Workspace $workspace = null, FileBag $fileBag = null): array
    {
        $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $data['tool']]);
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

            /* @var ImportToolEvent $event */
            $event = $this->dispatcher->dispatch(
                ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, $orderedTool->getTool()->getName()),
                ImportToolEvent::class,
                [$orderedTool->getTool()->getName(), AbstractTool::WORKSPACE, $orderedTool->getWorkspace(), $fileBag, $data['data'] ?? [], $newEntities]
            );

            return $event->getCreatedEntities();
        }

        return [];
    }
}
