<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/13 09:45:03
 */
class Version20150213094501 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_profile_property (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT DEFAULT NULL, 
                is_editable TINYINT(1) NOT NULL, 
                property VARCHAR(256) NOT NULL, 
                INDEX IDX_C2B93182D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_profile_property 
            ADD CONSTRAINT FK_C2B93182D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_profile_property
        ");
    }
}