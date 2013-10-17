<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/17 03:56:41
 */
class Version20131017155640 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_nonDigitalResourceType (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD nonDigitalResourceType_id INT DEFAULT NULL, 
            DROP type
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD CONSTRAINT FK_305E9E568CF60863 FOREIGN KEY (nonDigitalResourceType_id) 
            REFERENCES innova_nonDigitalResourceType (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_305E9E568CF60863 ON innova_nonDigitalResource (nonDigitalResourceType_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            DROP FOREIGN KEY FK_305E9E568CF60863
        ");
        $this->addSql("
            DROP TABLE innova_nonDigitalResourceType
        ");
        $this->addSql("
            DROP INDEX IDX_305E9E568CF60863 ON innova_nonDigitalResource
        ");
        $this->addSql("
            ALTER TABLE innova_nonDigitalResource 
            ADD type VARCHAR(255) NOT NULL, 
            DROP nonDigitalResourceType_id
        ");
    }
}