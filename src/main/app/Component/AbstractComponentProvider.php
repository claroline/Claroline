<?php

namespace Claroline\AppBundle\Component;

abstract class AbstractComponentProvider
{
    public function getComponent(string $componentName): ComponentInterface
    {
        $components = $this->getRegisteredComponents();
        foreach ($components as $component) {
            if ($component->getShortName() === $componentName)
            {
                return $component;
            }
        }

        throw new \Exception(sprintf('Component %s can not be found. Maybe its plugin is disabled or the component service does not have the correct tag.', $componentName));
    }

    abstract protected function getRegisteredComponents(): iterable;
}
