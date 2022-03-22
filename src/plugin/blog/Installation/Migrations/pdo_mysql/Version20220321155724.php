<?php

namespace Icap\BlogBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/03/21 03:57:26
 */
class Version20220321155724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            DROP FOREIGN KEY FK_95EB616FA76ED395
        ');
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            ADD CONSTRAINT FK_95EB616FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            DROP FOREIGN KEY FK_95EB616FA76ED395
        ');
        $this->addSql('
            ALTER TABLE icap__blog_comment 
            ADD CONSTRAINT FK_95EB616FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
    }
}
