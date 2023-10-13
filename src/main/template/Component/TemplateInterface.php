<?php

namespace Claroline\TemplateBundle\Component;

use Claroline\AppBundle\Component\ComponentInterface;

interface TemplateInterface extends ComponentInterface
{
    /**
     * Gets the usage of the template : email, pdf or other.
     */
    public static function getUsage(): string;

    /**
     * Gets the list of placeholders defined for the template.
     */
    public static function getPlaceholders(): array;

    /**
     * Gets the defined system templates.
     *
     * System templates are the base templates provided by the claroline platform.
     * We provide them as twig file to ensure there is at least one template for each type.
     * They are updated during platform/updates, can not be deleted nor edited.
     */
    public function getSystemTemplates(): array;
}
