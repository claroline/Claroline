<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/10/17
 */

namespace Claroline\CasBundle\Library\Sso;

use BeSimple\SsoAuthBundle\Sso\Cas\Server;

class CasServer extends Server
{
    private $indexUrl = null;
    /**
     * @return string
     */
    public function getLogoutUrl()
    {
        $serviceUrl = ($this->getIndexUrl() !== null) ? $this->getIndexUrl() : $this->getCheckUrl();
        $service = sprintf('service=%s', urlencode($serviceUrl));
        $url = $this->getLogoutTarget() ? sprintf('&url=%s', urlencode($this->getLogoutTarget())) : null;

        return sprintf('%s?%s%s', $this->getConfigValue('logout_url'), $service, $url);
    }

    public function setIndexUrl($indexUrl)
    {
        $this->indexUrl = $indexUrl;
    }

    public function getIndexUrl()
    {
        return $this->indexUrl;
    }

    public function updateConfig($config)
    {
        return $this->setConfig(array_merge($this->getConfig(), $config));
    }

    public function setCheckUrl($checkUrl)
    {
        if (!empty($checkUrl)) {
            $this->config['check_url'] = $checkUrl;
        }

        return $this;
    }
}
