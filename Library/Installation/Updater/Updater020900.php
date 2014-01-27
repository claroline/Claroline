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

class Updater020900
{
    private $container;
    private $logger;
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
        $this->log('backing up the badges...');
        $this->conn->query('CREATE TABLE claro_badge_rule_temp
                AS (SELECT * FROM claro_badge_rule)');

        //ignore the foreign keys for mysql
        $this->conn->query('truncate table claro_badge_rule');
    }

    public function postUpdate()
    {
        $this->log('Adding default mails...');
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('Claroline\CoreBundle\Entity\ContentTranslation');
        //mails
        $frTitle = 'Inscription à %platform_name%';
        $frContent = "<div>Votre nom d'utilisateur est %username%</div></br>";
        $frContent .= "<div>Votre mot de passe est %password%</div>";
        $enTitle = 'Registration to %platform_name%';
        $enContent = "<div>You username is %username%</div></br>";
        $enContent .= "<div>Your password is %password%</div>";
        $type = 'claro_mail_registration';
        $content = new Content();
        $content->setTitle($enTitle);
        $content->setContent($enContent);
        $content->setType($type);
        $repository->translate($content, 'title', 'fr', $frTitle);
        $repository->translate($content, 'content', 'fr', $frContent);
        $manager->persist($content);

        //layout
        $frLayout = '<div></div>%content%<div></hr>Powered by %platform_name%</div>';
        $enLayout = '<div></div>%content%<div></hr>Powered by %platform_name%</div>';
        $layout = new Content();
        $layout->setContent($enLayout);
        $layout->setType('claro_mail_layout');
        $repository->translate($layout, 'content', 'fr', $frLayout);
        $manager->persist($layout);

        $manager->flush();

        $this->updateUsers();
        $this->postUpdateBadges();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }

    private function updateUsers()
    {
        $this->log('Updating users...');

        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();
        $this->om->startFlushSuite();

        for ($i = 0, $count = count($users); $i < $count; ++$i) {

            $user = $users[$i];
            $this->log('updating ' . $user->getUsername() . '...');
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

    private function postUpdateBadges()
    {
        $this->log('Badges restoration...');
        $rowBadgeRules = $this->conn->query('SELECT * FROM claro_badge_rule_temp');

        foreach ($rowBadgeRules as $badgeRule) {
            $result = $badgeRule['result'] ? $this->conn->quote($badgeRule['result']): 'NULL';
            $resultComparison = $badgeRule['resultComparison'] ? $badgeRule['resultComparison']: 'NULL';
            $resourceId = $badgeRule['resource_id'] ? $badgeRule['resource_id']: 'NULL';

            $this->conn->query("INSERT INTO claro_badge_rule VALUES (
                {$badgeRule['id']},
                {$badgeRule['badge_id']},
                {$badgeRule['occurrence']},
                {$this->conn->quote($badgeRule['action'])},
                {$result},
                {$resultComparison},
                {$resourceId},
                {$badgeRule['badge_id']},
                0
            )");
        }

        $this->conn->query('DROP TABLE claro_badge_rule_temp');
    }
}
