<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Group;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;

class RemoveUser extends AbstractAction
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;

    /**
     * RemoveUser constructor.
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
        $group = $this->om->getObject($data['group'], Group::class, array_keys($data['group']));

        if ($user && $group) {
            $this->crud->patch($user, 'group', 'remove', [$group]);

            $successData['remove_user'][] = [
                'data' => $data,
                'log' => 'user removed from group.',
            ];
        }
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
        return ['group', 'remove_user'];
    }

    public function getBatchSize()
    {
        return 500;
    }
}
