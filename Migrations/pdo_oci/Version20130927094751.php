<?php

namespace Innova\PathBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/27 09:47:51
 */
class Version20130927094751 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_user2path
            DROP CONSTRAINT FK_2D4590E5A76ED395
        ");
        $this->addSql("
            ALTER TABLE innova_user2path
            DROP CONSTRAINT FK_2D4590E5D96C566B
        ");
        $this->addSql("
            ALTER TABLE innova_user2path
            ADD CONSTRAINT FK_2D4590E5A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE innova_user2path
            ADD CONSTRAINT FK_2D4590E5D96C566B FOREIGN KEY (path_id)
            REFERENCES innova_path (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_user2path
            DROP CONSTRAINT FK_2D4590E5A76ED395
        ");
        $this->addSql("
            ALTER TABLE innova_user2path
            DROP CONSTRAINT FK_2D4590E5D96C566B
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
}
