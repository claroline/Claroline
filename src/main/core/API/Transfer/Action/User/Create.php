<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;

class Create extends AbstractAction
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;

    /**
     * Create constructor.
     *
     * @param Crud          $crud
     * @param ObjectManager $om
     */
    public function __construct(Crud $crud, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->om = $om;
    }

    public function getAction()
    {
        return ['user', self::MODE_CREATE];
    }

    public function execute(array $data, &$successData = [])
    {
        $hasWs = false;
        $options = [];

        if (isset($data['meta']) && isset($data['meta']['personalWorkspace'])) {
            $hasWs = $data['meta']['personalWorkspace'];
        }

        if (!$hasWs) {
            $options[] = Options::NO_PERSONAL_WORKSPACE;
        }

        if (isset($data['mainOrganization'])) {
            $organization = $this->om->getObject($data['mainOrganization'], Organization::class, array_keys($data['mainOrganization']));

            if (!$organization) {
                throw new \Exception('Organization '.implode(',', $data['mainOrganization']).' does not exists');
            }
        }

        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $object = $this->om->getObject($group, Group::class, array_keys($group));

                if (!$object) {
                    throw new \Exception('Group '.implode(',', $group).' does not exists');
                }
            }
        }

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $role) {
                $object = $this->om->getObject($role, Role::class, array_keys($role));

                if (!$object) {
                    throw new \Exception('Role '.implode(',', $role).' does not exists');
                }
            }
        }

        $this->crud->create($this->getClass(), $data, $options);

        $successData['create'][] = [
            'data' => $data,
            'log' => $this->getAction()[0].' created.',
        ];
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        return ['$root' => $this->getClass()];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }

    public function getClass()
    {
        return User::class;
    }
}
