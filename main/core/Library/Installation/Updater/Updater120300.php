<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\MigrationBundle\Migrator\Migrator;
use Claroline\OpenBadgeBundle\Installation\Updater\Updater120300 as BadgeUpdater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Updater120300 extends Updater
{
    const BATCH_SIZE = 500;

    protected $logger;

    /** @var ObjectManager */
    private $om;

    /** @var TranslatorInterface */
    private $translator;

    private $parameters;

    private $contentRepo;
    private $translationRepo;
    private $templateRepo;
    private $templateTypeRepo;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->cryptoManager = $container->get('claroline.manager.cryptography_manager');
        $this->migrationManager = $container->get('claroline.migration.manager');
    }

    public function postUpdate()
    {
        $this->generateOrganizationKeys();
        //migrate badge tables
        $this->log('Executing migrations...');
        $bundle = $this->container->get('claroline.manager.plugin_manager')->getBundle('ClarolineOpenBadgeBundle');
        $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);

        $badgeUpdater = new BadgeUpdater($this->container, $this->logger);
        $badgeUpdater->setLogger($this->logger);
        $badgeUpdater->postUpdate();
    }

    private function generateOrganizationKeys()
    {
        $organizations = $this->om->getRepository(Organization::class)->findAll();

        foreach ($organizations as $orga) {
            $key = $this->om->getRepository(CryptographicKey::class)->findOneBy(['organization' => $orga]);

            if (!$key) {
                $this->log('Generate crypto for '.$orga->getName());
                $key = $this->cryptoManager->generatePair();
                $key->setOrganization($orga);
                $this->om->persist($key);
            }
        }

        $this->log('Flushing');
        $this->om->flush();
    }
}
