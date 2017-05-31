<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class InstallableBundle extends Bundle implements InstallableInterface
{
    public function hasMigrations()
    {
        return true;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return;
    }

    public function getPostInstallFixturesDirectory($environment)
    {
        return;
    }

    public function getOptionalFixturesDirectory($environment)
    {
        return;
    }

    public function getAdditionalInstaller()
    {
        return;
    }

    public function getComposer()
    {
        static $data;

        if (!$data) {
            $ds = DIRECTORY_SEPARATOR;
            $path = realpath($this->getPath().$ds.'composer.json');
            //metapackage are 2 directories above
            if (!$path) {
                $path = realpath($this->getPath()."{$ds}..{$ds}..{$ds}composer.json");
            }
            $data = json_decode(file_get_contents($path));
        }

        return $data;
    }

    public function getVersion()
    {
        $installed = $this->getInstalled();

        foreach ($installed as $package) {
            if ($package['name'] === $this->getComposerParameter('name')) {
                return $package['version'];
            }
        }
    }

    public function getOrigin()
    {
        return $this->getComposerParameter('name');
    }

    public function getDescription()
    {
        return file_exists($this->getPath().'/DESCRIPTION.md') ? file_get_contents($this->getPath().'/DESCRIPTION.md') : '';
    }

    private function getComposerParameter($parameter, $default = null)
    {
        $data = $this->getComposer();

        if ($data && property_exists($data, $parameter)) {
            return $data->{$parameter};
        }

        return $default;
    }

    public function getInstalled()
    {
        static $installed;

        if (!$installed) {
            $up = DIRECTORY_SEPARATOR.'..';
            //usual package
            $path = realpath($this->getPath().$up.$up.$up.'/vendor/composer/installed.json');
            //meta package
            if (!$path) {
                $path = realpath($this->getPath().$up.$up.$up.$up.$up.'/vendor/composer/installed.json');
            }
            $data = json_decode(file_get_contents($path), true);

            return $data;
        }
    }
}
