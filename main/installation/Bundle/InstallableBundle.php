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

    public function getOptionalFixturesDirectory($environment)
    {
        return;
    }

    public function getAdditionalInstaller()
    {
        return;
    }

    public function getVersion()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = realpath($this->getPath().$ds.'VERSION.txt');
        if ($path) {
            return trim(file_get_contents($path));
        }

        return '0.0.0.0';
    }

    public function getClarolineName()
    {
        $parts = explode('\\', get_class($this));

        return isset($parts[1]) ? $parts[1] : end($parts);
    }

    public function getType()
    {
        return $this->getComposerParameter('type', 'symfony-bundle');
    }

    public function getAuthors()
    {
        return $this->getComposerParameter('authors', []);
    }

    public function getDescription()
    {
        return $this->getComposerParameter('description');
    }

    public function getLicense()
    {
        return $this->getComposerParameter('license', '');
    }

    public function getTargetDir()
    {
        return $this->getComposerParameter('target-dir', '');
    }

    public function getBasePath()
    {
        return $this->getComposerParameter('name', $this->getName());
    }

    public function getComposer()
    {
        static $data;

        if (!$data) {
            $ds = DIRECTORY_SEPARATOR;
            $path = realpath($this->getPath().$ds.'composer.json');
            $data = json_decode(file_get_contents($path));
        }

        return $data;
    }

    private function getComposerParameter($parameter, $default = null)
    {
        $data = $this->getComposer();

        if ($data && property_exists($data, $parameter)) {
            return $data->{$parameter};
        }

        return $default;
    }
}
