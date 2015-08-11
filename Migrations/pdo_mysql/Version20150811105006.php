<?php

namespace FormaLibre\PresenceBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/11 10:50:07
 */
class Version20150811105006 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD status_id INT DEFAULT NULL, 
            DROP status
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD CONSTRAINT FK_33952B616BF700BD FOREIGN KEY (status_id) 
            REFERENCES formalibre_presencebundle_status (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_33952B616BF700BD ON formalibre_presencebundle_presence (status_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            DROP FOREIGN KEY FK_33952B616BF700BD
        ");
        $this->addSql("
            DROP INDEX IDX_33952B616BF700BD ON formalibre_presencebundle_presence
        ");
        $this->addSql("
            ALTER TABLE formalibre_presencebundle_presence 
            ADD status VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
            DROP status_id
        ");
    }
}