<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;

class SetMainOrganization extends AbstractAction
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(ObjectManager $om, Crud $crud)
    {
        $this->om = $om;
        $this->crud = $crud;
    }

    public function execute(array $data, &$successData = [])
    {
        /** @var User $user */
        $user = $this->om->getObject($data[$this->getAction()[0]], $this->getClass(), array_keys($data[$this->getAction()[0]]));

        /** @var Organization $organization */
        $organization = $this->om->getObject($data['organization'], Organization::class, array_keys($data['organization']));

        if (!empty($user) && !empty($organization)) {
            $this->crud->update($user, [
                'id' => $user->getUuid(),
                'mainOrganization' => [
                    'id' => $organization->getUuid(),
                ],
            ]);

            $successData['set_main_organization'][] = [
                'data' => $data,
                'log' => sprintf('%s added to organization %s.', $this->getAction()[0], $organization->getName()),
            ];
        }
    }

    public function getClass()
    {
        return User::class;
    }

    public function getAction()
    {
        return ['user', 'set_main_organization'];
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        return [
            'user' => User::class,
            'organization' => Organization::class,
        ];
    }
}
