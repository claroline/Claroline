<?php

namespace Claroline\BigBlueButtonBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/02 08:57:52
 */
class Version20200604090431 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_bigbluebuttonbundle_bbb (
                id INT AUTO_INCREMENT NOT NULL, 
                welcome_message VARCHAR(255) DEFAULT NULL, 
                end_message LONGTEXT DEFAULT NULL, 
                new_tab TINYINT(1) NOT NULL, 
                moderator_required TINYINT(1) NOT NULL, 
                record TINYINT(1) NOT NULL, 
                ratio DOUBLE PRECISION DEFAULT NULL, 
                activated TINYINT(1) NOT NULL, 
                server VARCHAR(255) DEFAULT NULL, 
                runningOn VARCHAR(255) DEFAULT NULL, 
                customUsernames TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CCC5E62ED17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_CCC5E62EB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_bigbluebuttonbundle_bbb 
            ADD CONSTRAINT FK_CCC5E62EB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_bigbluebuttonbundle_bbb
        ');
    }
}
