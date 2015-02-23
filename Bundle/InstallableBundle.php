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
        return null;
    }

    public function getOptionalFixturesDirectory($environment)
    {
        return null;
    }

    public function getAdditionalInstaller()
    {
        return null;
    }

    public function getVersion()
    {
        $data = $this->getComposer();
        if (property_exists($data, 'version')) return $data->version;

        return "0.0.0.0";
    }

    public function getClarolineName()
    {
        $data = $this->getComposer();
        $prop = 'target-dir';
        $parts = explode('/', $data->$prop);

        return end($parts);
    }

    public function getType()
    {
        $data = $this->getComposer();

        return $data->type;
    }

    public function getAuthors()
    {
        $data = $this->getComposer();
        if (property_exists($data, 'authors')) return $data->authors;

        return array();
    }

    public function getDescription()
    {
        $data = $this->getComposer();
        if (property_exists($data, 'description')) return $data->description;

        return null;
    }

    public function getLicense()
    {
        $data = $this->getComposer();
        if (property_exists($data, 'license')) return $data->license;

        return array();
    }

    public function getComposer()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = realpath($this->getPath() . $ds . 'composer.json');
        $data = json_decode(file_get_contents($path));

        return $data;
    }
}
