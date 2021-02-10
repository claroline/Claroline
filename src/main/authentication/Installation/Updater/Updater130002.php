<?php

namespace Claroline\AuthenticationBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class Updater130002 extends Updater
{
    /** @var string */
    private $configDir;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        string $configDir,
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        LoggerInterface $logger = null
    ) {
        $this->configDir = $configDir;
        $this->config = $config;
        $this->om = $om;
        $this->logger = $logger;
    }

    public function postUpdate()
    {
        // no need to do anything if no user attached to ips
        if (!$this->config->getParameter('security.default_root_anon_id')) {
            return;
        }

        $user = $this->om->getRepository(User::class)->findOneBy([
            'username' => $this->config->getParameter('security.default_root_anon_id'),
        ]);

        if (empty($user)) {
            return;
        }

        $ipFile = $this->configDir.DIRECTORY_SEPARATOR.'/ip_white_list.yml';
        if (file_exists($ipFile)) {
            $ips = Yaml::parseFile($ipFile);
            if (is_array($ips)) {
                $processed = [];
                foreach ($ips as $ip) {
                    if (!empty($ip) && !in_array($ip, $processed)) {
                        $ipUser = new IpUser();
                        $ipUser->setIp($ip);
                        $ipUser->setUser($user);

                        $this->om->persist($ipUser);
                        $processed[] = $ip;
                    }
                }
            }
        }

        $rangeFile = $this->configDir.DIRECTORY_SEPARATOR.'/white_list_ip_range.yml';
        if (file_exists($rangeFile)) {
            $ranges = Yaml::parseFile($rangeFile);
            if (is_array($ranges)) {
                foreach ($ranges as $range) {
                    if (!empty($range) {
                        $ipUser = new IpUser();
                        $ipUser->setIp(implode(',', array_values($range)));
                        $ipUser->setRange(true);
                        $ipUser->setUser($user);
                    }

                    $this->om->persist($ipUser);
                }
            }
        }

        $this->om->flush();
    }
}
