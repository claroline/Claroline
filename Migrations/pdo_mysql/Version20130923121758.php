<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 12:17:59
 */
class Version20130923121758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INT AUTO_INCREMENT NOT NULL,
                step_id INT DEFAULT NULL,
                resourceOrder INT NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                INDEX IDX_21EA11F73B21E9C (step_id),
                INDEX IDX_21EA11FB87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE innova_user2path (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                path_id INT NOT NULL,
                status INT NOT NULL,
                INDEX IDX_2D4590E5A76ED395 (user_id),
                INDEX IDX_2D4590E5D96C566B (path_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode
            ADD CONSTRAINT FK_21EA11F73B21E9C FOREIGN KEY (step_id)
            REFERENCES innova_step (id)
        ");
        $this->addSql("
            ALTER TABLE innova_step2resourceNode
            ADD CONSTRAINT FK_21EA11FB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE innova_user2path
            ADD CONSTRAINT FK_2D4590E5A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE innova_user2path
            ADD CONSTRAINT FK_2D4590E5D96C566B FOREIGN KEY (path_id)
            REFERENCES innova_path (id)
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_user2path
        ");
    }
}
