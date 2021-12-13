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

    public function register(InstallableInterface $bundle): Version
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

    public function execute(Version $version): void
    {
        $version->setIsUpgraded(true);

        $this->om->persist($version);
        $this->om->flush();
    }

    /**
     * Get the full version of the platform (eg. 13.1.2, 13.0.46).
     */
    public function getCurrent(): string
    {
        return trim($this->getVersionFile()[0]);
    }

    /**
     * Get the minor version of the platform (eg. 13.1, 12.5).
     */
    public function getCurrentMinor(): string
    {
        // remove patch version
        $versionParts = explode('.', $this->getCurrent());

        return $versionParts[0].'.'.$versionParts[1];
    }

    /**
     * Find the most recent Version installed of a bundle.
     */
    public function getLatestUpgraded(string $bundle): ?Version
    {
        try {
            return $this->repo->getLatestExecuted($bundle);
        } catch (\Exception $e) {
            //table is not here yet if version < 10
            return null;
        }
    }

    /**
     * Get the changelog for the current installed version.
     */
    public function getChangelogs(string $locale = 'en'): ?string
    {
        // we don't have changelogs for each patch version
        $minorVersion = $this->getCurrentMinor();

        $changelogs = null;
        if (file_exists("{$this->projectDir}/changelogs/changelog-{$minorVersion}.{$locale}.md")) {
            $changelogs = "{$this->projectDir}/changelogs/changelog-{$minorVersion}.{$locale}.md";
        } elseif (file_exists("{$this->projectDir}/changelogs/changelog-{$minorVersion}.en.md")) {
            // fallback to english version
            $changelogs = "{$this->projectDir}/changelogs/changelog-{$minorVersion}.en.md";
        }

        if ($changelogs) {
            return nl2br(file_get_contents($changelogs));
        }

        return null;
    }

    /**
     * Get the installation date of a specific version.
     * This is mostly used to know when to display changelog to administrators.
     */
    public function getInstallationDate(string $version): ?\DateTimeInterface
    {
        return $this->repo->getInstallationDate($version);
    }

    private function getVersionFile(): array
    {
        $data = file_get_contents($this->projectDir.'/VERSION.txt');

        return explode("\n", $data);
    }
}
