<?php

namespace Claroline\CoreBundle\Writer;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Plugin;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.writer.tool_writer")
 */
class ToolWriter
{
    /** @var EntityManager */
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function addRole(OrderedTool $otr, Role $role)
    {
        $otr->addRole($role);
        $this->save($otr);
    }

    public function removeRole(OrderedTool $otr, Role $role)
    {
        $otr->removeRole($role);
        $this->save($otr);
    }

    public function createOrderedTool(
        Tool $tool,
        $order,
        $name,
        AbstractWorkspace $workspace = null,
        User $user = null
    )
    {
        $orderedTool = new OrderedTool();
        $orderedTool->setWorkspace($workspace);
        $orderedTool->setName($name);
        $orderedTool->setOrder($order);
        $orderedTool->setUser($user);
        $orderedTool->setTool($tool);
        $this->save($orderedTool);

        return $orderedTool;
    }

    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
}
