<?php

namespace Claroline\AppBundle\Component;

abstract class AbstractComponentProvider implements ComponentProviderInterface
{
    public function getComponent(string $componentName): ComponentInterface
    {
        $components = $this->getRegisteredComponents();
        foreach ($components as $component) {
            if ($component::getName() === $componentName) {
                return $component;
            }
        }

        throw new \Exception(sprintf('Component %s can not be found. Maybe its plugin is disabled or the component service does not have the correct tag (expected tag : %s).', $componentName, static::getServiceTag()));
    }

    abstract protected function getRegisteredComponents(): iterable;
}
