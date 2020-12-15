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
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var WorkspaceSerializer */
    private $serializer;

    /**
     * Create constructor.
     *
     * @param ObjectManager       $om
     * @param Crud                $crud
     * @param WorkspaceManager    $workspaceManager
     * @param WorkspaceSerializer $serializer
     */
    public function __construct(
        ObjectManager $om,
        Crud $crud,
        WorkspaceManager $workspaceManager,
        WorkspaceSerializer $serializer
    ) {
        $this->om = $om;
        $this->crud = $crud;
        $this->workspaceManager = $workspaceManager;
        $this->serializer = $serializer;
    }

    public function execute(array $data, &$successData = [])
    {
        /** @var Workspace $workspace */
        $workspace = $this->crud->create($this->getClass(), $data);

        if (isset($data['model'])) {
            $model = $this->om->getRepository(Workspace::class)->findOneBy($data['model']);
        } else {
            $model = $this->workspaceManager->getDefaultModel();
        }

        if (!$model) {
            throw new \Exception('Model not found');
        }

        $workspace = $this->workspaceManager->copy($model, $workspace, false);
        // Override model values by the form ones. This is not the better way to do it
        // because it has already be done by Crud::create() earlier.
        // This is mostly because the model copy requires some of the target WS entities to be here (eg. Role).
        $workspace = $this->serializer->deserialize($data, $workspace);

        //add organizations here
        if (isset($data['organizations'])) {
            foreach ($data['organizations'] as $organizationData) {
                /** @var Organization $organization */
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
