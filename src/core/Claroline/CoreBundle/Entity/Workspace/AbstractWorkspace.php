<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use \RuntimeException;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Role;
use JMS\SerializerBundle\Annotation\Type;

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
    const PERSONNAL = 0;
    const STANDARD = 1;

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
     * @ORM\Column(type="integer", length=255)
     */
    protected $type;

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
     *     targetEntity="Claroline\CoreBundle\Entity\Rights\WorkspaceRights",
     *     mappedBy="workspace"
     * )
     */
    protected $rights;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Event",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $events;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->tools = new ArrayCollection();
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

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getRights()
    {
        return $this->rights;
    }
}