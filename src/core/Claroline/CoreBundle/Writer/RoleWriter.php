<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.writer.role_writer")
 */
class RoleWriter
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

//    public function create($name, $translation, $roleType, AbstractWorkspace $workspace = null, $parent = null)
//    {
//        $role = new Role();
//        $role->setName($name);
//        $role->setParent($parent);
//        $role->setType($roleType);
//        $role->setTranslationKey($translation);
//        $role->setWorkspace($workspace);
//
//        $this->em->persist($role);
//        $this->em->flush();
//
//        return $role;
//    }

//    public function bind(Role $role, AbstractRoleSubject $users)
//    {
//        $users->addRole($role);
//        $this->em->persist($users);
//        $this->em->flush();
//    }


    /////////// ?????????????

    public function createRole($name, $translationKey, $isReadOnly, $type, AbstractWorkspace $workspace = null)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType($type);
        $role->setWorkspace($workspace);

        $this->em->persist($role);
        $this->em->flush();

        return $role;
    }

    public function associateRole(AbstractRoleSubject $ars, Role $role)
    {
        $ars->addRole($role);

        $this->em->flush();
    }

    public function dissociateRole(AbstractRoleSubject $ars, Role $role)
    {
        $ars->removeRole($role);

        $this->em->flush();
    }
}