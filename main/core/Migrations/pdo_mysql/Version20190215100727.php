<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/02/15 10:07:28
 */
class Version20190215100727 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_object_lock (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                object_uuid VARCHAR(255) NOT NULL,
                object_class VARCHAR(255) NOT NULL,
                locked TINYINT(1) NOT NULL,
                creation_date DATETIME NOT NULL,
                INDEX IDX_9146967CA76ED395 (user_id),
                UNIQUE INDEX `unique` (object_uuid, object_class),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_object_lock
            ADD CONSTRAINT FK_9146967CA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_object_lock
        ');
    }
}
