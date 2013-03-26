<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\PluginRepository")
 * @ORM\Table(name="claro_plugin")
 */
class Plugin
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="vendor_name", type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\MaxLength(50)
     */
    protected $vendorName;

    /**
     * @ORM\Column(name="short_name", type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\MaxLength(50)
     */
    protected $bundleName;

    /**
     * @ORM\Column(name="has_options", type="boolean")
     */
    protected $hasOptions;

    /**
     * @ORM\Column(name="icon", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\MaxLength(255)
     */
    protected $icon;

    public function getId()
    {
        return $this->id;
    }

    public function getGeneratedId()
    {
        return $this->id;
    }

    public function getBundleFQCN()
    {
        $vendor = $this->getVendorName();
        $bundle = $this->getBundleName();

        return "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";
    }

    public function getShortName()
    {
        return strtolower($this->getVendorName() . str_replace('Bundle', '', $this->getBundleName()));
    }

    public function getVendorName()
    {
        return $this->vendorName;
    }

    public function setVendorName($name)
    {
        $this->vendorName = $name;
    }

    public function getBundleName()
    {
        return $this->bundleName;
    }

    public function setBundleName($name)
    {
        $this->bundleName = $name;
    }

    public function setHasOptions($hasOptions)
    {
        $this->hasOptions = $hasOptions;
    }

    public function hasOptions()
    {
        return $this->hasOptions;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }
}