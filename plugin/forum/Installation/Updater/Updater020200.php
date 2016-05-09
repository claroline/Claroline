<?php

namespace Claroline\ForumBundle\Installation\Updater;

use Claroline\ForumBundle\Entity\Category;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater020200 extends Updater
{
    private $container;
    /** @var  Connection */
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function preUpdate()
    {
        if (!in_array('claro_forum_category', $this->conn->getSchemaManager()->listTableNames())) {
            $this->log('backing up the forum subjects...');
            $this->conn->query('CREATE TABLE claro_forum_subject_temp
                AS (SELECT * FROM claro_forum_subject)');
            $this->log('backing up the forum messages...');
            $this->conn->query('CREATE TABLE claro_forum_message_temp
                AS (SELECT * FROM claro_forum_message)');

            $this->log('truncating the previous table...');

            //ignore the foreign keys for mysql
            $this->conn->query('SET FOREIGN_KEY_CHECKS=0');
            $this->conn->query('truncate table claro_forum_subject');
            $this->conn->query('truncate table claro_forum_message');
            $this->conn->query('SET FOREIGN_KEY_CHECKS=1');
        } else {
            $this->log('category already added');
        }
    }

    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $process = false;

        if (in_array('claro_forum_subject_temp', $this->conn->getSchemaManager()->listTableNames())) {
            $columns = $this->conn->getSchemaManager()->listTableColumns('claro_forum_subject_temp');

            foreach ($columns as $column) {
                if ($column->getName() === 'forum_id') {
                    $process = true;
                    break;
                }
            }
        }

        if ($process) {
            $this->log('restoring the subjects...');
            $forums = $em->getRepository('ClarolineForumBundle:Forum')->findAll();
            $sql = 'SELECT * FROM claro_forum_subject_temp WHERE forum_id = :forumId';
            $stmt = $this->conn->prepare($sql);

            foreach ($forums as $forum) {
                $category = new Category();
                $category->setName($forum->getResourceNode()->getName());
                $category->setForum($forum);
                $em->persist($category);
                $em->flush();
                $stmt->bindValue('forumId', $forum->getId());
                $stmt->execute();

                foreach ($stmt->fetchAll() as $rowsSubject) {
                    $this->conn->query("INSERT INTO claro_forum_subject VALUES (
                        {$rowsSubject['id']},
                        {$category->getId()},
                        {$rowsSubject['user_id']},
                        {$this->conn->quote($rowsSubject['title'])},
                        '{$rowsSubject['created']}',
                        '{$rowsSubject['updated']}',
                        false
                    )");
                }
            }

            $this->log('restoring the messages...');
            $this->conn->query('INSERT IGNORE INTO claro_forum_message SELECT * FROM claro_forum_message_temp');
            $this->conn->query('DROP TABLE claro_forum_message_temp');
            $this->conn->query('DROP TABLE claro_forum_subject_temp');
            $this->conn->query('DROP TABLE claro_forum_options');
        } else {
            $this->log('categories already added');
        }

        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findBy(array('name' => 'claroline_forum_widget'));

        if (!$widget) {
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
            $plugin->setHasOptions(true);
            $em->persist($widget);
            $em->flush();
        } else {
            $this->log('forum widget already added');
        }
    }
}
