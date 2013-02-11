<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use \RuntimeException;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\SerializerBundle\Annotation\Type;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\WorkspaceTool;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace"
 *         = "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace",
 *     "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace"
 *         = "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace"
 * })
 */
abstract class AbstractWorkspace
{
    protected static $visitorPrefix = 'ROLE_WS_VISITOR';
    protected static $collaboratorPrefix = 'ROLE_WS_COLLABORATOR';
    protected static $managerPrefix = 'ROLE_WS_MANAGER';
    protected static $customPrefix = 'ROLE_WS_CUSTOM';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    protected $isPublic = true;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *     mappedBy="workspace"
     * )
     */
    protected $resources;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Event",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $events;

    /**
     * @ORM\OneToMany(
<<<<<<< HEAD
     *     targetEntity="Claroline\CoreBundle\Entity\Event",
=======
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole",
>>>>>>> 88b6b16784f39380ffcab09b8756434d5d16f1d8
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $workspaceToolRoles;


    /**
     * @ORM\OneToMany(
     * targetEntity="Claroline\CoreBundle\Entity\Role",
     * mappedBy="workspace",
     * cascade={"persist"}
     * )
     */
    protected $roles;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="personalWorkspace"
     * )
     */
    protected $personalUser;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->workspaceTools = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = 0;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    abstract public function setPublic($isPublic);

    public function isPublic()
    {
        return $this->isPublic;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getWorkspaceToolRoles()
    {
        return $this->workspaceToolRoles;
    }
}