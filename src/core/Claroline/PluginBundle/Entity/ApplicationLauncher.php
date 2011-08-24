<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\PluginBundle\Entity\Application;
use Claroline\SecurityBundle\Entity\Role;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_application_launcher")
 */
class ApplicationLauncher
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="launchers")
     */
    protected $application;

    /**
     * @ORM\Column(name="route_id", type="string", length="255")
     */
    protected $routeId;

    /**
     * @ORM\Column(name="translation_key", type="string", length="255")
     */
    protected $translationKey;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\SecurityBundle\Entity\Role", cascade={"persist"}))
     * @ORM\JoinTable(name="claro_launcher_role",
     *     joinColumns={@ORM\JoinColumn(name="launcher_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")})
     */
    protected $accessRoles;

    public function __construct()
    {
        $this->accessRoles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    public function getRouteId()
    {
        return $this->routeId;
    }

    public function setRouteId($routeId)
    {
        $this->routeId = $routeId;
    }

    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    public function setTranslationKey($key)
    {
        $this->translationKey = $key;
    }

    public function getAccessRoles()
    {
        return $this->accessRoles->toArray();
    }

    public function addAccessRole(Role $role)
    {
        $this->accessRoles->add($role);
    }
}