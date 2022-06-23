<?php

namespace Claroline\TagBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/04/25 08:45:34
 */
class Version20220425084528 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_tagbundle_tag 
            DROP FOREIGN KEY FK_6E5EC9DA76ED395
        ');
        $this->addSql('
            DROP INDEX `unique` ON claro_tagbundle_tag
        ');
        $this->addSql('
            DROP INDEX IDX_6E5EC9DA76ED395 ON claro_tagbundle_tag
        ');

        $this->addSql('
            DELETE to1
            FROM claro_tagbundle_tagged_object AS to1 
            LEFT JOIN claro_tagbundle_tag AS t1 ON (to1.tag_id = t1.id)
            WHERE EXISTS (
                SELECT to2.id
                FROM (SELECT * FROM claro_tagbundle_tagged_object) AS to2 
                LEFT JOIN claro_tagbundle_tag AS t2 ON (to2.tag_id = t2.id)
                WHERE to2.object_id = to1.object_id
                  AND t2.tag_name = t1.tag_name
                  AND to2.id != to1.id
            )
        ');

        // generate a platform tag when there are user tag but not platform one
        $this->addSql('
            INSERT INTO claro_tagbundle_tag (tag_name, uuid)
                SELECT DISTINCT t1.tag_name, UUID() as uuid
                FROM claro_tagbundle_tag AS t1
                WHERE t1.user_id IS NOT NULL
                  AND NOT EXISTS (
                      SELECT t3.id
                      FROM (SELECT * from claro_tagbundle_tag) AS t3
                      WHERE t3.tag_name = t1.tag_name
                        AND t1.id != t3.id
                        AND t3.user_id IS NULL
                  )
                GROUP BY t1.tag_name
        ');

        // linked tagged objects to the platform tags (the ones without user)
        $this->addSql('
            UPDATE claro_tagbundle_tagged_object AS tob
            LEFT JOIN claro_tagbundle_tag AS t1 ON (tob.tag_id = t1.id)
            LEFT JOIN claro_tagbundle_tag AS t2 ON (t1.tag_name = t2.tag_name)
            SET tob.tag_id = t2.id
            WHERE t1.user_id IS NOT NULL
              AND t2.user_id IS NULL
              AND NOT EXISTS (
                SELECT tob2.id 
                FROM (SELECT * FROM claro_tagbundle_tagged_object) AS tob2
                WHERE tob2.tag_id = t2.id
                  AND tob2.object_id = tob.object_id
                  AND tob2.id != tob.id
              )
        ');

        // delete user tags when there is a platform tag
        $this->addSql('
            DELETE t1
            FROM claro_tagbundle_tag AS t1 
            LEFT JOIN claro_tagbundle_tag AS t2 ON (t1.tag_name = t2.tag_name)
            WHERE t1.user_id IS NOT NULL
              AND t2.user_id IS NULL
              AND t2.id IS NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_tagbundle_tag 
            DROP user_id
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6E5EC9DB02CC1B0 ON claro_tagbundle_tag (tag_name)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_6E5EC9DB02CC1B0 ON claro_tagbundle_tag
        ');
        $this->addSql('
            ALTER TABLE claro_tagbundle_tag 
            ADD user_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_tagbundle_tag 
            ADD CONSTRAINT FK_6E5EC9DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX `unique` ON claro_tagbundle_tag (tag_name, user_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_6E5EC9DA76ED395 ON claro_tagbundle_tag (user_id)
        ');
    }
}
