<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Group;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;

class AddUser extends AbstractAction
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;

    /**
     * AddUser constructor.
     *
     * @param Crud          $crud
     * @param ObjectManager $om
     */
    public function __construct(Crud $crud, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->om = $om;
    }

    public function execute(array $data, &$successData = [])
    {
        $user = $this->om->getObject($data['user'], User::class, array_keys($data['user']));
        if (!$user) {
            throw new \Exception('User does not exists');
        }

        $group = $this->om->getObject($data['group'], Group::class, array_keys($data['group']));
        if (!$group) {
            throw new \Exception('Group does not exists');
        }

        $this->crud->patch($group, 'user', 'add', [$user]);

        $successData['add_user'][] = [
            'data' => $data,
            'log' => 'user registered to group.',
        ];
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
