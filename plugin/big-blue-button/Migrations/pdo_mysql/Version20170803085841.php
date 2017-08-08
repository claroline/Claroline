<?php

namespace Claroline\BigBlueButtonBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/08/03 08:58:45
 */
class Version20170803085841 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_bigbluebuttonbundle_bbb (
                id INT AUTO_INCREMENT NOT NULL, 
                room_name VARCHAR(255) DEFAULT NULL, 
                welcome_message VARCHAR(255) DEFAULT NULL, 
                new_tab TINYINT(1) NOT NULL, 
                moderator_required TINYINT(1) NOT NULL, 
                record TINYINT(1) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CCC5E62EB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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
