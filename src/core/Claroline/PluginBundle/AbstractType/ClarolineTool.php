<?php

namespace Claroline\PluginBundle\AbstractType;

abstract class ClarolineTool extends ClarolinePlugin
{
    abstract public function setContext($context);
}