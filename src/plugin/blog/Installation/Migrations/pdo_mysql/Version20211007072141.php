<?php

namespace Icap\BlogBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/07 07:21:47
 */
class Version20211007072141 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__blog_post 
            DROP FOREIGN KEY FK_1B067922A76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_1B067922A76ED395 ON icap__blog_post
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD author VARCHAR(255) DEFAULT NULL, 
            CHANGE user_id creator_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD CONSTRAINT FK_1B06792261220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_1B06792261220EA6 ON icap__blog_post (creator_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__blog_post 
            DROP FOREIGN KEY FK_1B06792261220EA6
        ');
        $this->addSql('
            DROP INDEX IDX_1B06792261220EA6 ON icap__blog_post
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            DROP author, 
            CHANGE creator_id user_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD CONSTRAINT FK_1B067922A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_1B067922A76ED395 ON icap__blog_post (user_id)
        ');
    }
}
