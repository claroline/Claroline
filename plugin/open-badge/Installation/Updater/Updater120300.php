<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Installation\Updater;

use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\File\PublicFileUse;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class Updater120300 extends Updater
{
    const BATCH_SIZE = 100;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;

        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->config = $container->get('claroline.config.platform_config_handler');
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->makePublicBadgeImagePublicFiles();
        $this->truncateTables();
        $this->log('Migrating badges...');
        $this->migrateBadges();
        $this->migrateAssertions();
        $this->removeOldBadgeTools();
    }

    public function removeOldBadgeTools()
    {
        $this->log('Removing old badge tools');

        $tool = $this->om->getRepository(Tool::class)->findOneByName('badges');
        if ($tool) {
            $this->om->remove($tool);
        }
        $tool = $this->om->getRepository(Tool::class)->findOneByName('my_badges');
        if ($tool) {
            $this->om->remove($tool);
        }
        $tool = $this->om->getRepository(Tool::class)->findOneByName('all_my_badges');
        if ($tool) {
            $this->om->remove($tool);
        }
        $tool = $this->om->getRepository(AdminTool::class)->findOneByName('badges_management');
        if ($tool) {
            $this->om->remove($tool);
        }
        $this->om->flush();
    }

    public function truncateTables()
    {
        $tables = [
            'claro__open_badge_badge_class',
            'claro__open_badge_assertion',
        ];

        foreach ($tables as $table) {
            $this->truncate($table);
        }
    }

    private function truncate($table)
    {
        try {
            $this->log('TRUNCATE '.$table);
            $sql = '
                SET FOREIGN_KEY_CHECKS=0;
                TRUNCATE TABLE '.$table.';
                SET FOREIGN_KEY_CHECKS=1;
            ';

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } catch (\Exception $e) {
            $this->log('Couldnt truncate '.$table);
        }
    }

    private function makePublicBadgeImagePublicFiles()
    {
        $this->log('Deleting previous migrated images...');
        $sql = "DELETE FROM `claro_public_file` WHERE directory_name = 'uploads/badges'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $this->log('Building image public files...');

        $sql = 'SELECT * FROM `claro_badge`';
        $badges = $this->conn->query($sql);

        $i = 0;

        while ($row = $badges->fetch()) {
            ++$i;

            $this->log('Migrating image '.$row['image'].' '.$i);

            $publicFile = $this->om->getRepository(PublicFile::class)->findOneByUrl('data/uploads/badges/'.$row['image']);
            $author = $this->om->getRepository(User::class)->findOneByUsername('claroline-connect');

            if (!$publicFile) {
                $file = $this->container->getParameter('claroline.param.files_directory').'/data/uploads/badges/'.$row['image'];
                try {
                    $sfFile = new File($file);
                    $mimeType = $sfFile->getMimeType();
                    $size = filesize($file);
                } catch (\Exception $e) {
                    $mimeType = $size = null;
                    $this->log('File '.$file.' '.' not found', LogLevel::ERROR);
                }
                $publicFile = new PublicFile();
                $publicFile->setDirectoryName('uploads/badges');
                $publicFile->setFilename($row['image']);
                $publicFile->setSize($size);
                $publicFile->setMimeType($mimeType);
                $publicFile->setCreationDate(new \DateTime());
                $publicFile->setUrl('data/uploads/badges/'.$row['image']);
                $publicFile->setSourceType(null);
                $publicFile->setCreator($author);

                $publicFileUse = new PublicFileUse();
                $publicFileUse->setPublicFile($publicFile);
                $publicFileUse->setObjectClass(BadgeClass::class);
                $publicFileUse->setObjectUuid($row['uuid']);
                $publicFileUse->setObjectName(null);

                $this->om->persist($publicFile);
                $this->om->persist($publicFileUse);
            }

            if (0 === $i % self::BATCH_SIZE) {
                $this->log('Flush.');
                $this->om->flush();
            }
        }

        $this->om->flush();
    }

    private function migrateBadges()
    {
        $mainOrganization = $this->om->getRepository(Organization::class)->findOneBy(['default' => true]);

        if (count($this->om->getRepository(BadgeClass::class)->findAll()) > 0) {
            $this->log('Badge already migrated.');
        } else {
            $this->log('BadgeClass migration.');
            $sql = '
              INSERT INTO claro__open_badge_badge_class (
                id, uuid, image, enabled, description, criteria, name, issuer_id
              )
              SELECT temp.id, temp.uuid, CONCAT("data/uploads/badges/", temp.image), true, trans.description, trans.criteria, trans.name, '.$mainOrganization->getId().' FROM claro_badge temp
              JOIN claro_badge_translation trans ON trans.badge_id = temp.id
              WHERE trans.locale = "fr"';

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            //get name from claro_badge_translation
        }
    }

    private function migrateAssertions()
    {
        if (count($this->om->getRepository(Assertion::class)->findAll()) > 0) {
            $this->log('Assertion already migrated.');
        } else {
            $this->log('Assertion migration.');
            $sql = '
            INSERT INTO claro__open_badge_assertion (
              id, uuid, recipient_id, badge_id, revoked, issuedOn
            )
            SELECT temp.id, (SELECT UUID()) as uuid, temp.user_id, temp.badge_id, 0, temp.claimed_at FROM claro_badge_claim temp';

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }
}
