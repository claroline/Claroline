<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Update\Version;
use Claroline\CoreBundle\Library\PluginBundleInterface;
use Claroline\CoreBundle\Repository\VersionRepository;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Psr\Log\LoggerAwareInterface;

class VersionManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    /** @var VersionRepository */
    private $repo;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;

        $this->repo = $this->om->getRepository('ClarolineCoreBundle:Update\Version');
    }

    public function register(InstallableInterface $bundle)
    {
        $data = $this->getVersionFile();

        /** @var Version $version */
        $version = $this->repo->findOneBy(['version' => $data[0], 'bundle' => $bundle->getBundleFQCN()]);

        if (!empty($version)) {
            $this->log(
                sprintf('Version "%s" of "%s" already registered !', trim($version->getVersion()), $version->getBundle())
            );

            return $version;
        }

        $this->log("Registering {$bundle->getBundleFQCN()} version {$data[0]}");
        $version = new Version($data[0], $data[1], $data[2], $bundle->getBundleFQCN());
        $this->om->persist($version);
        $this->om->flush();

        return $version;
    }

    public function execute(Version $version)
    {
        $version->setIsUpgraded(true);
        $this->om->persist($version);
        $this->om->flush();
    }

    public function getCurrent()
    {
        return trim($this->getVersionFile()[0]);
    }

    /**
     * @param string $bundle
     */
    public function getLatestUpgraded($bundle)
    {
        $fqcn = $bundle instanceof PluginBundleInterface ? $bundle->getBundleFQCN() : $bundle;

        try {
            return $this->repo->getLatestExecuted($fqcn);
        } catch (\Exception $e) {
            //table is not here yet if version < 10
            return null;
        }
    }

    public function getVersionFile()
    {
        $data = file_get_contents(__DIR__.'/../../../VERSION.txt');

        return explode("\n", $data);
    }
}
