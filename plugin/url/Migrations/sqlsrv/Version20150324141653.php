<?php

namespace HeVinci\UrlBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/24 02:16:56
 */
class Version20150324141653 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_url (
                id INT IDENTITY NOT NULL, 
                url NVARCHAR(255) NOT NULL, 
                nom NVARCHAR(50) NOT NULL, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_ECB39474B87FAB32 ON claro_url (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_url 
            ADD CONSTRAINT FK_ECB39474B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_url
        ');
    }
}
