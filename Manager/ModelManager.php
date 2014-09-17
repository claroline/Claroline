<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Entity\Model\ResourceModel;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.model_manager")
 */
class ModelManager
{
    private $om;
    private $modelRepository;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om
    )
    {
        $this->om = $om;
        $this->modelRepository = $this->om->getRepository('ClarolineCoreBundle:Model\WorkspaceModel');
    }

    public function create($name, Workspace $workspace)
    {
        $model = new WorkspaceModel();
        $model->setName($name);
        $model->setWorkspace($workspace);
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    public function edit(WorkspaceModel $model, $name)
    {
        $model->setName($name);
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    public function delete(WorkspaceModel $model)
    {
        $this->om->remove($model);
        $this->om->flush();
    }

    public function getByWorkspace(Workspace $workspace)
    {
        return $this->modelRepository->findByWorkspace($workspace);
    }

    public function addUser(WorkspaceModel $model, User $user)
    {
        $model->addUser($user);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function addGroup(WorkspaceModel $model, Group $group)
    {
        $model->addGroup($group);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function removeGroup(WorkspaceModel $model, Group $group)
    {
        $group->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function removeUser(WorkspaceModel $model, User $user)
    {
        $user->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function addUsers(WorkspaceModel $model, array $users)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->addUser($model, $user);
        }

        $this->om->endFlushSuite();
    }

    public function addGroups(WorkspaceModel $model, array $groups)
    {
        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $this->addGroup($model, $group);
        }

        $this->om->endFlushSuite();
    }

    public function addResourceNodes(WorkspaceModel $model, array $resourceNodes, $isCopy)
    {
        $this->om->startFlushSuite();
        $resourceModels = [];

        foreach ($resourceNodes as $resourceNode) {
            $resourceModels[] = $this->addResourceNode($model, $resourceNode, $isCopy);
        }

        $this->om->endFlushSuite();

        return $resourceModels;
    }

    public function addResourceNode(WorkspaceModel $model, ResourceNode $resourceNode, $isCopy)
    {
        $resourceModel = new ResourceModel();
        $resourceModel->setModel($model);
        $resourceModel->setResourceNode($resourceNode);
        $resourceModel->setIsCopy($isCopy);
        $this->om->persist($resourceModel);
        $this->om->flush();

        return $resourceModel;
    }

    public function removeResourceModel(ResourceModel $resourceModel)
    {
        $this->om->remove($resourceModel);
        $this->om->flush();
    }

    public function addHomeTabs(WorkspaceModel $model, array $homeTabs)
    {
        $this->om->startFlushSuite();

        foreach ($homeTabs as $homeTab) {
            $this->addHomeTab($model, $homeTab);
        }

        $this->om->endFlushSuite();
    }

    public function addHomeTab(WorkspaceModel $model, HomeTab $homeTab)
    {
        $model->addHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function removeHomeTab(WorkspaceModel $model, HomeTab $homeTab)
    {
        $model->removeHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function updateHomeTabs(WorkspaceModel $model, array $homeTabs)
    {
        $this->om->startFlushSuite();
        $oldHomeTabs = $model->getHomeTabs();

        foreach ($oldHomeTabs as $oldHomeTab) {
            $search = array_search($oldHomeTab, $homeTabs, true);

            if ($search !== false) {
                unset($homeTabs[$search]);
            } else {
                $this->removeHomeTab($model, $oldHomeTab);
            }
        }
        $this->addHomeTabs($model, $homeTabs);
        $this->om->endFlushSuite();
    }

    public function toArray(WorkspaceModel $model)
    {
        $array = [];
        $array['name'] = $model->getName();

        return $array;
    }
}