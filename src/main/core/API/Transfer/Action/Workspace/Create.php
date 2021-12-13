<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class Create extends AbstractAction
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(
        ObjectManager $om,
        Crud $crud
    ) {
        $this->om = $om;
        $this->crud = $crud;
    }

    public function execute(array $data, &$successData = [])
    {
        /** @var Workspace $workspace */
        $workspace = $this->crud->create($this->getClass(), $data);

        if (isset($data['managers'])) {
            foreach ($data['managers'] as $manager) {
                $user = $this->om->getRepository(User::class)->findOneBy($manager);
                $role = $this->om->getRepository(Role::class)->findOneBy(['workspace' => $workspace, 'translationKey' => 'manager']);
                if ($role) {
                    $this->crud->patch($user, 'role', 'add', [$role]);
                } else {
                    throw new \Exception('Could not find role manager');
                }
            }
        }

        $successData['create'][] = [
            'data' => $data,
            'log' => $this->getAction()[0].' created.',
        ];
    }

    public function getClass()
    {
        return Workspace::class;
    }

    public function getAction()
    {
        return ['workspace', self::MODE_CREATE];
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        return ['$root' => $this->getClass()];
    }

    public function getOptions()
    {
        //in an ideal world this should be removed but for now it's an easy fix
        return [Options::FORCE_FLUSH];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }
}
