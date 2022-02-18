<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class Create extends AbstractImporter
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

    public function execute(array $data): array
    {
        /** @var Workspace $workspace */
        $workspace = $this->crud->create(Workspace::class, $data, [Options::FORCE_FLUSH]);

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

        return [
            'create' => [[
                'data' => $data,
                'log' => $this->getAction()[0].' created.',
            ]],
        ];
    }

    public function getAction(): array
    {
        return ['workspace', self::MODE_CREATE];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return ['$root' => Workspace::class];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }
}
