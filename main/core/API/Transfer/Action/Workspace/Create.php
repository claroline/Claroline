<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;

class Create extends AbstractAction
{
    /**
     * Action constructor.
     *
     * @param Crud $crud
     */
    public function __construct(ObjectManager $om, Crud $crud, WorkspaceManager $workspaceManager, WorkspaceSerializer $serializer)
    {
        $this->crud = $crud;
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->serializer = $serializer;
    }

    public function execute(array $data, &$successData = [])
    {
        $workspace = $this->crud->create(
            $this->getClass(),
            $data,
            [Options::LIGHT_COPY]
        );

        if (isset($data['model'])) {
            $model = $this->om->getRepository(Workspace::class)->findOneBy($data['model']);
        } else {
            $model = $this->workspaceManager->getDefaultModel();
        }

        if (!$model) {
            throw new \Exception('Model not found');
        }

        $workspace = $this->workspaceManager->copy($model, $workspace, false);

        //add organizations here
        if (isset($data['organizations'])) {
            foreach ($data['organizations'] as $organizationData) {
                $organization = $this->om->getRepository(Organization::class)->findOneBy($organizationData);
                $workspace->addOrganization($organization);
                $this->om->persist($workspace);
            }
        }

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

        $this->om->flush();

        $successData['create'][] = [
          'data' => $data,
          'log' => $this->getAction()[0].' created.',
        ];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Workspace\Workspace';
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
        //in an ideal world this should be removedn but for now it's an easy fix
        return [Options::FORCE_FLUSH];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }
}
