<?php
namespace Claroline\CoreBundle\Library\Workgroup;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Role;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.workgroup.manager")
 */
class Manager
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct()
    {
        $this->em = $em;
    }

    public function create($name, AbstractWorkspace $workspace)
    {
        $role = new Role();
        $role->setName($name);
        $role->setWorkspace($workspace);
        $role->setType(Role::CUSTOM_ROLE);

        $this->em->persist($role);
        $this->em->flush();
    }
}
