<?php

namespace Claroline\ForumBundle\Installation\Updater;

use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater120000 extends Updater
{
    private $container;
    /** @var Connection */
    private $conn;
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function preUpdate()
    {
        try {
            $this->log('backing up the forum subjects...');
            $this->conn->query('CREATE TABLE claro_forum_subject_temp_new  AS (SELECT * FROM claro_forum_subject)');
        } catch (\Exception $e) {
            $this->log('Coulnt backup forum subjects');
        }
    }

    public function postUpdate()
    {
        $this->log('restoring the categories as tag...');

        $sql = 'SELECT * FROM claro_forum_subject_temp_new ';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $this->restoreSubjectCategories();
        $this->createForumUsers();
        $this->flagFirstMessage();
    }

    private function restoreSubjectCategories()
    {
        $sql = "
          SELECT COUNT(object.id) as count
          FROM claro_tagbundle_tagged_object object
          WHERE object.object_class LIKE 'Claroline\\ForumBundle\\Entity\\Subject'
        ";
        $stmt = $this->executeSql($sql);
        $count = $stmt->fetchColumn(0);

        if ((int) $count > 0) {
            $this->log('Already done...');

            return;
        }

        //step 1: restore forum subject
        $this->log('Set subject forums...');

        $sql = "
            UPDATE claro_forum_subject subject
            JOIN claro_forum_subject_temp_new tmp on tmp.id = subject.id
            JOIN claro_forum_category category on tmp.category_id = category.id
            SET subject.forum_id = category.forum_id,
            subject.moderation = 'NONE'
        ";

        $this->executeSql($sql);

        //step 2: restore tags from categories
        $this->log('Insert tags...');

        $sql = '
            INSERT INTO claro_tagbundle_tag (tag_name)
            SELECT DISTINCT category.name
            FROM claro_forum_category category
            JOIN claro_forum forum on category.forum_id = forum.id
            JOIN claro_resource_node node on forum.resourceNode_id = node.id
            LEFT JOIN claro_tagbundle_tag tag on tag.tag_name = category.name
            WHERE tag.id IS NULL
        ';

        $this->executeSql($sql, true);

        $this->log('Insert tagged objects...');

        $sql = "
            INSERT INTO claro_tagbundle_tagged_object (tag_id, object_class, object_id, object_name)
            SELECT DISTINCT tag.id, 'Claroline\\\\ForumBundle\\\\Entity\\\\Subject', subject.uuid, subject.title
            FROM claro_forum_category category
            JOIN claro_forum_subject_temp_new tmp on tmp.category_id = category.id
            JOIN claro_forum_subject subject on tmp.id = subject.id
            JOIN claro_tagbundle_tag tag on tag.tag_name = category.name
            JOIN claro_forum forum on category.forum_id = forum.id
            JOIN claro_resource_node node on forum.resourceNode_id = node.id
            JOIN claro_user clarouser on node.creator_id = clarouser.id
            WHERE category.name = tag.tag_name

        ";

        $this->executeSql($sql);
    }

    private function executeSql($sql, $force = false)
    {
        $stmt = $this->conn->prepare($sql);

        if (!$force) {
            $stmt->execute();
        } else {
            try {
                $stmt->execute();
            } catch (\Exception $e) {
                $this->log($sql.'; Failed to be executed');
            }
        }

        return $stmt;
    }

    private function createForumUsers()
    {
        $users = $this->om->getRepository(User::class)->findAll();

        if (0 === count($users)) {
            $this->log('Build forum users...');

            $sql = '
                INSERT INTO claro_forum_user (user_id, forum_id, access, notified, banned)
                SELECT DISTINCT user.id, forum.id, false, false, false
                FROM claro_forum forum
                JOIN claro_forum_category category ON category.forum_id = forum.id
                JOIN claro_forum_subject_temp_new subject ON subject.category_id = category.id
                JOIN claro_forum_message message ON message.subject_id = subject.id
                JOIN claro_user user on message.user_id = user.id
            ';

            $this->executeSql($sql);
        } else {
            $this->log('Users already loaded...');
        }
    }

    private function flagFirstMessage()
    {
        //this is sql wizzardy on multiple levels
        //keep first
        //https://stackoverflow.com/questions/19414474/sql-select-distinct-but-keep-first
        //and UPDATE
        //https://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause

        $sql = '
            UPDATE claro_forum_message
            SET first = true
            WHERE id IN (
              SELECT * from (SELECT message.id FROM claro_forum_message message GROUP BY message.subject_id ORDER BY MIN(id) ASC)
              as t
            )
        ';

        $this->executeSql($sql, true);
    }
}
