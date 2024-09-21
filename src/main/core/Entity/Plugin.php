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

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_plugin')]
#[ORM\UniqueConstraint(name: 'plugin_unique_name', columns: ['vendor_name', 'short_name'])]
#[ORM\Entity(repositoryClass: \Claroline\CoreBundle\Repository\PluginRepository::class)]
class Plugin
{
    use Id;

    #[ORM\Column(name: 'vendor_name', length: 50)]
    protected $vendorName;

    #[ORM\Column(name: 'short_name', length: 50)]
    protected $bundleName;

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
