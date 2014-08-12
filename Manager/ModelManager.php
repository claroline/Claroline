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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Model\Model;
use Claroline\CoreBundle\Entity\Model\ResourceModel;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;

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
        $this->modelRepository = $this->om->getRepository('ClarolineCoreBundle:Model\Model');
    }

    public function create($name, Workspace $workspace)
    {
        $model = new Model();
        $model->setName($name);
        $model->setWorkspace($workspace);
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    public function edit(Model $model, $name)
    {
        $model->setName($name);
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    public function delete(Model $model)
    {
        $this->om->remove($model);
        $this->om->flush();
    }

    public function getByWorkspace(Workspace $workspace)
    {
        return $this->modelRepository->findByWorkspace($workspace);
    }

    public function addUser(Model $model, User $user)
    {
        $model->addUser($user);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function addGroup(Model $model, Group $group)
    {
        $model->addGroup($group);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function removeGroup(Model $model, Group $group)
    {
        $group->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function removeUser(Model $model, User $user)
    {
        $user->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function addUsers(Model $model, array $users)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->addUser($model, $user);
        }

        $this->om->endFlushSuite();
    }

    public function addGroups(Model $model, array $groups)
    {
        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $this->addGroup($model, $group);
        }

        $this->om->endFlushSuite();
    }

    public function addResourceNodes(Modle $model, array $resourceNodes, $isCopy)
    {
        $this->om->startFlushSuite();

        foreach ($resourceNodes as $resourceNode) {
            $this->addResourceNode($model, $resourceNode);
        }

        $this->om->endFlushSuite();
    }

    public function addResourceNode(Model $model, ResourceNode $resourceNode, $isCopy)
    {
        $resourceModel = new ResourceModel();
        $resourceModel->setModel($model),
        $resourceModel->setResourceNode($resourceNode);
        $resourceModel->setIsCopy($isCopy);
        $this->om->persist($resourceModel);
        $this->om->flush();
    }

    public function removeResourceModel(ResourceModel $resourceModel)
    {
        $this->om->remove($resourceModel);
        $this->ol->flush();
    }

    public function addHomeTabs(Model $model, array $homeTabs)
    {
        $this->om->startFlushSuite();

        foreach ($homeTabs as $homeTab) {
            $this->addHomeTab($model, $homeTab);
        }

        $this->om->endFlushSuite();
    }

    public function addHomeTab(Model $model, HomeTab $homeTab)
    {
        $model->addHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function removeHomeTab(Model $model, HomeTab $homeTab)
    {
        $model->removeHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    public function toArray(Model $model)
    {
        $array = [];
        $array['name'] = $model->getName();

        return $array;
    }
}