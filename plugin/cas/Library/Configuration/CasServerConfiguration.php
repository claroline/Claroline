<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/6/17
 */

namespace Claroline\CasBundle\Library\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;

class CasServerConfiguration implements ParameterProviderInterface
{
    const DEFAULT_LOGIN = 'default';
    const PRIMARY_LOGIN = 'primary';

    private $id = 'cas';
    private $version = 2;
    private $loginUrl;
    private $logoutUrl;
    private $validationUrl;
    private $active = false;
    private $loginOption = self::DEFAULT_LOGIN;
    private $name = 'CAS';
    private $loginTargetRoute;

    public function __construct(
        $isActive = false,
        $loginUrl = null,
        $logoutUrl = null,
        $validationUrl = null,
        $loginOption = self::DEFAULT_LOGIN,
        $name = 'CAS',
        $loginTargetRoute = null
    ) {
        $this->active = $isActive;
        $this->loginOption = $loginOption;
        $this->loginUrl = $loginUrl;
        $this->logoutUrl = $logoutUrl;
        $this->validationUrl = $validationUrl;
        $this->name = $name;
        $this->loginTargetRoute = $loginTargetRoute;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * @param mixed $loginUrl
     *
     * @return $this
     */
    public function setLoginUrl($loginUrl)
    {
        $this->loginUrl = $loginUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogoutUrl()
    {
        return $this->logoutUrl;
    }

    /**
     * @param mixed $logoutUrl
     *
     * @return $this
     */
    public function setLogoutUrl($logoutUrl)
    {
        $this->logoutUrl = $logoutUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidationUrl()
    {
        return $this->validationUrl;
    }

    /**
     * @param mixed $validationUrl
     *
     * @return $this
     */
    public function setValidationUrl($validationUrl)
    {
        $this->validationUrl = $validationUrl;

        return $this;
    }

    public function getConfigurationArray()
    {
        return [
            'protocol' => [
                'id' => $this->id,
                'version' => $this->version,
            ],
            'server' => [
                'id' => $this->id,
                'login_url' => $this->loginUrl,
                'logout_url' => $this->logoutUrl,
                'validation_url' => $this->validationUrl,
            ],
        ];
    }

    public function getNonEmptyConfigurationArray()
    {
        $conf = $this->getConfigurationArray();
        if (empty($this->loginUrl)) {
            $conf['server']['login_url'] = 'http://cas.server.tld/login';
        }
        if (empty($this->logoutUrl)) {
            $conf['server']['logout_url'] = 'http://cas.server.tld/logout';
        }
        if (empty($this->validationUrl)) {
            $conf['server']['validation_url'] = 'http://cas.server.tld/serviceValidate';
        }

        return $conf;
    }

    public function getDefaultParameters()
    {
        return [
            'cas_server_login_active' => false,
            'cas_server_login_option' => self::DEFAULT_LOGIN,
            'cas_server_login_url' => null,
            'cas_server_logout_url' => null,
            'cas_server_validation_url' => null,
            'cas_server_login_name' => 'CAS',
        ];
    }

    public function getParameters()
    {
        return [
            'cas_server_login_active' => $this->active,
            'cas_server_login_option' => $this->loginOption,
            'cas_server_login_url' => $this->loginUrl,
            'cas_server_logout_url' => $this->logoutUrl,
            'cas_server_validation_url' => $this->validationUrl,
            'cas_server_login_name' => $this->name,
        ];
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOverrideLogin()
    {
        return strpos($this->loginTargetRoute, 'claro_cas_') !== false;
    }

    /**
     * @return string
     */
    public function getLoginOption()
    {
        return $this->loginOption;
    }

    /**
     * @param string $loginOption
     *
     * @return $this
     */
    public function setLoginOption($loginOption)
    {
        $this->loginOption = $loginOption;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
