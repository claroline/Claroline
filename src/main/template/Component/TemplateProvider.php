<?php

namespace Claroline\TemplateBundle\Component\Template;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the templates defined in the Claroline app.
 */
class TemplateProvider extends AbstractComponentProvider
{
    /**
     * The list of all the templates injected in the app by the current plugins.
     * It does not contain templates for disabled plugins.
     */
    private iterable $registeredTemplates = [];

    public function __construct(iterable $registeredTemplates)
    {
        $this->registeredTemplates = $registeredTemplates;
    }

    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredTemplates;
    }
}
