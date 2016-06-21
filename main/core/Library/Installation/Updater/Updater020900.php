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

use Claroline\CoreBundle\Entity\Content;
use Claroline\InstallationBundle\Updater\Updater;

class Updater020900 extends Updater
{
    private $container;
    private $om;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function preUpdate()
    {
        $this->backupBadges();
    }

    public function postUpdate()
    {
        $this->restoreBadges();
        $this->updateUsers();
        $this->insertDefaultMails();
        $this->updateFrontController();
    }

    private function backupBadges()
    {
        $schema = $this->conn->getSchemaManager();

        if (!$schema->listTableDetails('claro_badge_rule')->hasColumn('badge_id')
            && !$schema->tablesExist(array('claro_badge_rule_temp'))) {
            $this->log('Backing up the badges...');
            $this->conn->exec(
                'CREATE TABLE claro_badge_rule_temp
                AS (SELECT * FROM claro_badge_rule)'
            );
            // ignore the foreign keys for mysql
            $this->conn->exec('TRUNCATE TABLE claro_badge_rule');
        }
    }

    private function restoreBadges()
    {
        if ($this->conn->getSchemaManager()->tablesExist(array('claro_badge_rule_temp'))) {
            $this->log('Restoring badges...');
            $rowBadgeRules = $this->conn->query('SELECT * FROM claro_badge_rule_temp');

            foreach ($rowBadgeRules as $badgeRule) {
                $result = $badgeRule['result'] ? $this->conn->quote($badgeRule['result']) : 'NULL';
                $resultComparison = $badgeRule['resultComparison'] ? $badgeRule['resultComparison'] : 'NULL';
                $resourceId = $badgeRule['resource_id'] ? $badgeRule['resource_id'] : 'NULL';

                $this->conn->query(
                    "INSERT INTO claro_badge_rule VALUES (
                        {$badgeRule['id']},
                        {$badgeRule['badge_id']},
                        {$badgeRule['occurrence']},
                        {$this->conn->quote($badgeRule['action'])},
                        {$result},
                        {$resultComparison},
                        {$resourceId},
                        {$badgeRule['badge_id']},
                        0
                    )"
                );
            }

            $this->conn->exec('DROP TABLE claro_badge_rule_temp');
        }
    }

    private function updateUsers()
    {
        $this->log('Updating users...');

        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();
        $this->om->startFlushSuite();

        for ($i = 0, $count = count($users); $i < $count; ++$i) {
            $user = $users[$i];
            $this->log('updating '.$user->getUsername().'...');
            $user->setIsEnabled(true);
            $user->setIsMailNotified(false);
            $this->om->persist($user);

            if ($i % 200 === 0) {
                $this->om->endFlushSuite();
                $this->om->startFlushSuite();
            }
        }

        $this->om->endFlushSuite();
    }

    private function insertDefaultMails()
    {
        $this->log('Adding default mails...');
        $repository = $this->om->getRepository('Claroline\CoreBundle\Entity\ContentTranslation');
        //mails
        $frTitle = 'Inscription à %platform_name%';
        $frContent = "<div>Votre nom d'utilisateur est %username%</div></br>";
        $frContent .= '<div>Votre mot de passe est %password%</div>';
        $enTitle = 'Registration to %platform_name%';
        $enContent = '<div>You username is %username%</div></br>';
        $enContent .= '<div>Your password is %password%</div>';
        $type = 'claro_mail_registration';
        $content = new Content();
        $content->setTitle($enTitle);
        $content->setContent($enContent);
        $content->setType($type);
        $repository->translate($content, 'title', 'fr', $frTitle);
        $repository->translate($content, 'content', 'fr', $frContent);
        $this->om->persist($content);

        //layout
        $frLayout = '<div></div>%content%<div></hr>Powered by %platform_name%</div>';
        $enLayout = '<div></div>%content%<div></hr>Powered by %platform_name%</div>';
        $layout = new Content();
        $layout->setContent($enLayout);
        $layout->setType('claro_mail_layout');
        $repository->translate($layout, 'content', 'fr', $frLayout);
        $this->om->persist($layout);

        $this->om->flush();
    }

    private function updateFrontController()
    {
        $this->log('Updating front controller...');
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $webDir = realpath("{$rootDir}/../web");
        $sourcePath = realpath(
            "{$rootDir}/../vendor/claroline/core-bundle/Resources/web/app.php"
        );
        $targetPath = "{$webDir}/app.php";

        if (is_writable($webDir) && file_exists($targetPath) && @unlink($targetPath)) {
            copy($sourcePath, $targetPath);
        } else {
            $msg = 'WARNING: This updater cannot modify the front controller web/app.php '
                .'due to file permissions issues. If you want to benefit from the new '
                ."maintenance mode, replace the file '{$targetPath}' by '{$sourcePath}'.";
            $this->log("<comment>{$msg}</comment>");
        }
    }
}
