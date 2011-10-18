<?php

namespace Claroline\PluginBundle\AbstractType;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class ClarolinePlugin extends Bundle
{
    final public function getType()
    {
        return get_parent_class($this);
    }

    final public function getVendorNamespace()
    {
        $namespaceParts = explode('\\', $this->getNamespace());

        return $namespaceParts[0];
    }

    final public function getBundleName()
    {
        $namespaceParts = explode('\\', $this->getNamespace());

        return $namespaceParts[1];
    }

    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = $this->getPath().$ds.'Resources'.$ds.'config'.$ds.'routing.yml';

        if (file_exists($path))
        {
            return array($path);
        }
        
        return null;
    }

    public function getRoutingPrefix()
    {
        $vendor = $this->getVendorNamespace();
        $prefix = $this->getBundleName();
        $pattern = '#^(.+)Bundle$#';
        
        if (preg_match($pattern, $prefix, $matches))
        {
            $prefix = $matches[1];
        }
        
        $prefix = strtolower("{$vendor}_{$prefix}");
        
        return $prefix;
    }
    
    public function getNameTranslationKey()
    {
        return 'No available translated name';
    }

    public function getDescriptionTranslationKey()
    {
        return 'No available description';
    }
}