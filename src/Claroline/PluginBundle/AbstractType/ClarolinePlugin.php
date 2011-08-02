<?php

namespace Claroline\PluginBundle\AbstractType;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Finder\Finder;
use Doctrine\ORM\Tools\SchemaTool;

abstract class ClarolinePlugin extends Bundle
{
    public function getEntityDirectory(){}

    public function getVendorNamespace()
    {
        $namespaceParts = explode('\\', $this->getNamespace());

        return $namespaceParts[0];
    }

    public function getBundleName()
    {
        $namespaceParts = explode('\\', $this->getNamespace());

        return $namespaceParts[1];
    }

    public function getRoutingResourcesPaths()
    {
        $path = $this->getPath()
              . DIRECTORY_SEPARATOR
              . 'Resources'
              . DIRECTORY_SEPARATOR
              . 'config'
              . DIRECTORY_SEPARATOR
              . 'routing.yml';

        if (file_exists($path))
        {
            return array($path);
        }
        
        return null;
    }
}