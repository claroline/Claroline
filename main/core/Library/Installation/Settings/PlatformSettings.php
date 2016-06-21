<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Settings;

class PlatformSettings extends AbstractValidator
{
    private $language;
    private $name = 'Claroline';
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

    public function setSupportEmail($email)
    {
        $this->supportEmail = $email;
    }

    public function getSupportEmail()
    {
        return $this->supportEmail;
    }

    /**
     * @param mixed $organizationUrl
     */
    public function setOrganizationUrl($organizationUrl)
    {
        $this->organizationUrl = $organizationUrl;
    }

    /**
     * @return mixed
     */
    public function getOrganizationUrl()
    {
        return $this->organizationUrl;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param array $settings
     */
    public function bindData(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (method_exists($this, $method = 'set'.ucfirst($name))) {
                $this->{$method}($value);
            }
        }
    }

    protected function doValidate()
    {
        $this->checkIsNotBlank('language', $this->language);
        $this->checkIsNotBlank('name', $this->name);

        if ($this->checkIsNotBlank('supportEmail', $this->supportEmail)) {
            $this->checkIsValidEmail('supportEmail', $this->supportEmail);
        }

        if (!empty($this->organizationUrl)) {
            $this->checkIsValidUrl('organizationUrl', $this->organizationUrl);
        }
    }
}
