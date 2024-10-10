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
use Claroline\CoreBundle\Repository\PluginRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_plugin')]
#[ORM\UniqueConstraint(name: 'plugin_unique_name', columns: ['vendor_name', 'short_name'])]
#[ORM\Entity(repositoryClass: PluginRepository::class)]
class Plugin
{
    use Id;

    #[ORM\Column(name: 'vendor_name', length: 50)]
    protected ?string $vendorName = null;

    #[ORM\Column(name: 'short_name', length: 50)]
    protected ?string $bundleName = null;

    public function getBundleFQCN(): string
    {
        $vendor = $this->getVendorName();
        $bundle = $this->getBundleName();

        return "{$vendor}\\{$bundle}\\{$vendor}{$bundle}";
    }

    public function getShortName(): string
    {
        return strtolower($this->getVendorName().'_'.str_replace('Bundle', '', $this->getBundleName()));
    }

    public function getVendorName(): ?string
    {
        return $this->vendorName;
    }

    public function setVendorName(string $name): void
    {
        $this->vendorName = $name;
    }

    public function getBundleName(): ?string
    {
        return $this->bundleName;
    }

    public function setBundleName(string $name): void
    {
        $this->bundleName = $name;
    }

    public function getSfName(): ?string
    {
        return $this->vendorName.$this->bundleName;
    }

    public function __toString()
    {
        return $this->getBundleFQCN();
    }
}
