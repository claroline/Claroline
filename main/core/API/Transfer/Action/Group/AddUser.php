<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Group;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;

class AddUser extends AbstractAction
{
    /**
     * Action constructor.
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->om = $om;
    }

    public function execute(array $data, &$successData = [])
    {
        $user = $this->om->getRepository(User::class)->findOneBy($data['user']);
        $group = $this->om->getRepository(Group::class)->findOneBy($data['group']);

        if (!$user) {
            throw new \Exception('User does not exists');
        }

        if (!$group) {
            throw new \Exception('Group does not exists');
        }

        $this->crud->patch($group, 'user', 'add', [$user]);
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        return ['group' => Group::class, 'user' => User::class];
    }

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    public function getAction()
    {
        return ['group', 'add_user'];
    }

    public function getBatchSize()
    {
        return 500;
    }

    public function clear(ObjectManager $om)
    {
    }
}
