<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/13/17
 */

namespace Claroline\CasBundle\Library\Sso;

use BeSimple\SsoAuthBundle\Sso\Factory;
use BeSimple\SsoAuthBundle\Sso\Manager;
use Buzz\Client\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CasFactory extends Factory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /** @var array */
    private $managers;

    public function __construct(ContainerInterface $container, ClientInterface $client)
    {
        parent::__construct($container, $client);
        $this->container = $container;
        $this->managers = [];
    }
    /**
     * @param string $id
     * @param string $checkUrl
     *
     * @return Manager
     */
    public function getManager($id, $checkUrl = null)
    {
        if (!isset($this->managers[$id])) {
            $this->managers[$id] = parent::getManager($id, $checkUrl);

            return $this->managers[$id];
        }

        $server = $this->managers[$id]->getServer();
        if ($server->getCheckUrl() === null) {
            if (empty($checkUrl)) {
                $checkUrl = $this->container->get('router')->generate('claro_security_login_check', [], true);
            }
            $server->setCheckUrl($checkUrl);
            $server->setIndexUrl($this->container->get('router')->generate('claro_index', [], true));
        }

        return $this->managers[$id];
    }

    public function updateServerConfig($id, $config, $checkUrl = null)
    {
        $manager = $this->getManager($id, $checkUrl);
        $manager->getServer()->updateConfig($config);
    }
}
