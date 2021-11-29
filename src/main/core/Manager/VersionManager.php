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
    /** @var string */
    private $projectDir;

    public function __construct(
        ObjectManager $om,
        string $projectDir
    ) {
        $this->om = $om;
        $this->projectDir = $projectDir;

        $this->repo = $this->om->getRepository(Version::class);
    }

    public function register(InstallableInterface $bundle)
    {
        $data = $this->getVersionFile();

        /** @var Version $version */
        $version = $this->repo->findOneBy(['version' => $data[0], 'bundle' => get_class($bundle)]);

        if (!empty($version)) {
            $this->log(
                sprintf('Version "%s" of "%s" already registered !', trim($version->getVersion()), $version->getBundle())
            );

            return $version;
        }

        $this->log(sprintf('Registering %s version %s', get_class($bundle), $data[0]));
        $version = new Version($data[0], $data[1], $data[2], get_class($bundle));
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

    public function getLatestUpgraded(string $bundle)
    {
        try {
            return $this->repo->getLatestExecuted($bundle);
        } catch (\Exception $e) {
            //table is not here yet if version < 10
            return null;
        }
    }

    public function getVersionFile()
    {
        $data = file_get_contents($this->projectDir.'/VERSION.txt');

        return explode("\n", $data);
    }
}
