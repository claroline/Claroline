<?php

namespace Claroline\ForumBundle\Installation\Updater;

use Claroline\ForumBundle\Entity\Category;
use Claroline\CoreBundle\Entity\Widget\Widget;

class Updater020200
{

    private $container;
    private $logger;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function preUpdate()
    {
//        $this->log('backing up the forum subjects...');
//        $this->conn->query('CREATE TABLE claro_forum_subject_temp
//            AS (SELECT * FROM claro_forum_subject)');
//        $this->log('backing up the forum messages...');
//        $this->conn->query('CREATE TABLE claro_forum_message_temp
//            AS (SELECT * FROM claro_forum_message)');
//
//        $this->log('truncating the previous table...');
//
//        //ignore the forign keys for mysql
//        $this->conn->query('SET FOREIGN_KEY_CHECKS=0');
//        $this->conn->query('truncate table claro_forum_subject');
//        $this->conn->query('truncate table claro_forum_message');
//        $this->conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function postUpdate()
    {
//        $this->log('restoring the subjects...');
        $em = $this->container->get('doctrine.orm.entity_manager');
//        $forums = $em->getRepository('ClarolineForumBundle:Forum')->findAll();
//
//
//        foreach ($forums as $forum) {
//            $category = new Category();
//            $category->setName($forum->getResourceNode()->getName());
//            $category->setForum($forum);
//            $em->persist($category);
//            $em->flush();
//            $rowsSubjects = $this->conn->query('SELECT * FROM claro_forum_subject_temp WHERE forum_id = ' . $forum->getId());
//
//            foreach ($rowsSubjects as $rowsSubject) {
//                $this->conn->query("INSERT INTO claro_forum_subject VALUES (
//                    {$rowsSubject['id']},
//                    {$category->getId()},
//                    {$rowsSubject['user_id']},
//                    {$this->conn->quote($rowsSubject['title'])},
//                    '{$rowsSubject['created']}',
//                    '{$rowsSubject['updated']}'
//                )");
//            }
//        }
//
//        $this->log('restoring the messages...');
//        $this->conn->query('INSERT IGNORE INTO claro_forum_message SELECT * FROM claro_forum_message_temp');
//        $this->conn->query('DROP TABLE claro_forum_message_temp');
//        $this->conn->query('DROP TABLE claro_forum_subject_temp');

        $this->log('adding the forum widget...');

        $plugin = $em->getRepository('ClarolineCoreBundle:Plugin')
            ->findOneBy(array('vendorName' => 'Claroline', 'bundleName' => 'ForumBundle'));

        $widget = new Widget();
        $widget->setName('claroline_forum_widget');
        $widget->setDisplayableInDesktop(true);
        $widget->setDisplayableInWorkspace(true);
        $widget->setConfigurable(false);
        $widget->setExportable(false);
        $widget->setIcon('none');
        $widget->setPlugin($plugin);
        $em->persist($widget);
        $em->flush();
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
}