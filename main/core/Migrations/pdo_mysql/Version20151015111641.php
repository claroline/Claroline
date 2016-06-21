<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/10/15 11:16:45
 */
class Version20151015111641 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_api_client 
            ADD friendRequest_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_api_client 
            ADD CONSTRAINT FK_233AC88FD6215480 FOREIGN KEY (friendRequest_id) 
            REFERENCES claro_friend_request (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_233AC88FD6215480 ON claro_api_client (friendRequest_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_api_client 
            DROP FOREIGN KEY FK_233AC88FD6215480
        ');
        $this->addSql('
            DROP INDEX IDX_233AC88FD6215480 ON claro_api_client
        ');
        $this->addSql('
            ALTER TABLE claro_api_client 
            DROP friendRequest_id
        ');
    }
}
