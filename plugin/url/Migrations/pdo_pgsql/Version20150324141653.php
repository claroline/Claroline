<?php

namespace HeVinci\UrlBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/24 02:16:55
 */
class Version20150324141653 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_url (
                id SERIAL NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                nom VARCHAR(50) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_ECB39474B87FAB32 ON claro_url (resourceNode_id)
        ');
        $this->addSql('
            ALTER TABLE claro_url 
            ADD CONSTRAINT FK_ECB39474B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_url
        ');
    }
}
