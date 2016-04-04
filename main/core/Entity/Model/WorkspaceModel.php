<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Model;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="claro_workspace_model",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="workspace_model_unique_name_workspace",
 *             columns={"name", "workspace_id"}
 *         )
 *     }
 * )
 * @ORM\Entity
 * @DoctrineAssert\UniqueEntity({"name", "workspace"})
 */
class WorkspaceModel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     inversedBy="models", cascade={"merge"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="models"
     * )
     */
    protected $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Group",
     *     mappedBy="models"
     * )
     */
    protected $groups;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Model\ResourceModel",
     *     mappedBy="model"
     * )
     */
    protected $resourcesModel;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Home\HomeTab"
     * )
     * @ORM\JoinTable(name="claro_workspace_model_home_tab")
     */
    protected $homeTabs;

    public function __construct()
    {
        $this->users          = new ArrayCollection();
        $this->groups         = new ArrayCollection();
        $this->resourcesModel = new ArrayCollection();
        $this->homeTabs       = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function addUser(User $user)
    {
        $user->addModel($this);
    }

    public function addGroup(Group $group)
    {
        $group->addModel($this);
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function addResourceModel(ResourceModel $resourceModel)
    {
        $this->resourcesModel = $resourceModel;
    }

    public function getResourcesModel()
    {
        return $this->resourcesModel;
    }

    public function addHomeTab(HomeTab $homeTab)
    {
        $this->homeTabs->add($homeTab);
    }

    public function removeHomeTab(HomeTab $homeTab)
    {
        $this->homeTabs->removeElement($homeTab);
    }

    public function getHomeTabs()
    {
        return $this->homeTabs;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getNameAndWorkspace()
    {
        return $this->name .
            ' (' .
            $this->workspace->getName() .
            ' [' .
            $this->workspace->getCode() .
            '])';
    }
}