<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\PluginRepository")
 * @ORM\Table(
 *      name="claro_plugin",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="plugin_unique_name", columns={"vendor_name", "short_name"})
 *      }
 * )
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
     * @ORM\Column(name="vendor_name", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    protected $vendorName;

    /**
     * @ORM\Column(name="short_name", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    protected $bundleName;

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
        return strtolower($this->getVendorName().'_'.str_replace('Bundle', '', $this->getBundleName()));
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

    public function getSfName()
    {
        return $this->vendorName.$this->bundleName;
    }

    //for debugging
    public function __toString()
    {
        return $this->getBundleFQCN();
    }
}
