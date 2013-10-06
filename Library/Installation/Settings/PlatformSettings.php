<?php

namespace Claroline\CoreBundle\Library\Installation\Settings;

class PlatformSettings extends AbstractValidator
{
    private $language;
    private $name;
    private $organization;
    private $organizationUrl;
    private $supportEmail;

    public function setLanguage($language)
    {
        $this->language = trim($language);
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $settings
     */
    public function bindData(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (method_exists($this, $method = 'set' . ucfirst($name))) {
                $this->{$method}($value);
            }
        }
    }

    protected function doValidate()
    {
        $this->checkIsNotBlank('language', $this->language);
        $this->checkIsNotBlank('name', $this->name);
    }
}
