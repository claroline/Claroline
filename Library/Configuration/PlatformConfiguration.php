<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Configuration;

class PlatformConfiguration
{
    private $name;
    private $supportEmail;
    private $selfRegistration;
    private $localeLanguage;
    private $theme;
    private $footer;
    private $role;
    private $termsOfService;
    private $cookieLifetime;
    private $mailerTransport;
    private $mailerHost;
    private $mailerUsername;
    private $mailerPassword;
    private $mailerAuthenticationMode;
    private $mailerEncryption;
    private $mailerPort;

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function getLocaleLanguage()
    {
        return $this->localeLanguage;
    }

    public function setLocaleLanguage($localeLanguage)
    {
        $this->localeLanguage = $localeLanguage;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSupportEmail()
    {
        return $this->supportEmail;
    }

    public function setSupportEmail($email)
    {
        $this->supportEmail = $email;
    }

    public function getFooter()
    {
        return $this->footer;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    public function setDefaultRole($role)
    {
        $this->role = $role;
    }

    public function getDefaultRole()
    {
        return $this->role;
    }

    public function setTermsOfService($termsOfService)
    {
        $this->termsOfService = $termsOfService;
    }

    public function getTermsOfService()
    {
        return $this->termsOfService;
    }

    public function setCookieLifetime($time)
    {
        $this->cookieLifetime = $time;
    }

    public function getCookieLifetime()
    {
        return $this->cookieLifetime;
    }

    public function setMailerTransport($transport)
    {
        $this->mailerTransport = $transport;
    }

    public function getMailerTransport()
    {
        return $this->mailerTransport;
    }

    public function setMailerHost($host)
    {
        $this->mailerHost = $host;
    }

    public function getMailerHost()
    {
        return $this->mailerHost;
    }

    public function setMailerUsername($username)
    {
        $this->mailerUsername = $username;
    }

    public function getMailerUsername()
    {
        return $this->mailerUsername;
    }

    public function setMailerPassword($password)
    {
        $this->mailerPassword = $password;
    }

    public function getMailerPassword()
    {
        return $this->mailerPassword;
    }

    public function setMailerEncryption($encryption)
    {
        $this->mailerEncryption = $encryption;
    }

    public function getMailerEncryption()
    {
        return $this->mailerEncryption;
    }

    public function setMailerPort($port)
    {
        $this->mailerPort = $port;
    }

    public function getMailerPort()
    {
        return $this->mailerPort;
    }

    public function setMailerAuthenticationMode($mode)
    {
        $this->mailerAuthenticationMode = $mode;
    }

    public function getMailerAuthenticationMode()
    {
        return $this->mailerAuthenticationMode;
    }
}
