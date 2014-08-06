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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Model\Model;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;

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

    public function delete(Model $model)
    {
        $this->om->remove($model);
        $this->om->flush();
    }

    public function getByWorkspace(Workspace $workspace)
    {
        return $this->modelRepository->findByWorkspace($workspace);
    }

    public function addResource(Model $model, ResourceNode $node, $isLink)
    {

    }

    public function removeResource(Model $model, ResourceNode $node)
    {

    }

    public function addHomeTab(Model $model, HomeTab $homeTab)
    {

    }

    public function removeHomeTab(Model $model, HomeTab $homeTab)
    {

    }
    //sharing

    public function addUserShare(Model $model, User $user)
    {

    }

    public function removeUsersShare(Model $model, User $user)
    {

    }

    public function addGroupShare(Model $model, Group $group)
    {

    }

    public function removeGroupsShare(Model $model, Group $group)
    {

    }

    public function toArray(Model $model)
    {
        $array = [];
        $array['name'] = $model->getName();

        return $array;
    }
}