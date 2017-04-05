<?php

namespace Claroline\ClacoFormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/03/31 10:07:37
 */
class Version20170331100735 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_clacoformbundle_entry_user (
                id INT AUTO_INCREMENT NOT NULL, 
                entry_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                shared TINYINT(1) NOT NULL, 
                notify_edition TINYINT(1) NOT NULL, 
                notify_comment TINYINT(1) NOT NULL, 
                notify_vote TINYINT(1) NOT NULL, 
                INDEX IDX_7036190CBA364942 (entry_id), 
                INDEX IDX_7036190CA76ED395 (user_id), 
                UNIQUE INDEX clacoform_unique_entry_user (entry_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry_user 
            ADD CONSTRAINT FK_7036190CBA364942 FOREIGN KEY (entry_id) 
            REFERENCES claro_clacoformbundle_entry (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_entry_user 
            ADD CONSTRAINT FK_7036190CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql("
            ALTER TABLE claro_clacoformbundle_field 
            ADD locked TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD locked_edition TINYINT(1) DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_clacoformbundle_entry_user
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field 
            DROP locked, 
            DROP locked_edition
        ');
    }
}
