<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/03/29 05:25:54
 */
class Version20180329172553 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
          CREATE TABLE claro_import_file (
              id INT AUTO_INCREMENT NOT NULL,
              file_id INT DEFAULT NULL,
              log VARCHAR(255) DEFAULT NULL,
              status VARCHAR(255) DEFAULT NULL,
              action VARCHAR(255) DEFAULT NULL,
              start_date DATETIME DEFAULT NULL,
              executionDate DATETIME DEFAULT NULL,
              uuid VARCHAR(36) NOT NULL,
              UNIQUE INDEX UNIQ_EA6FE9F1D17F50A6 (uuid),
              INDEX IDX_EA6FE9F193CB796C (file_id),
              PRIMARY KEY(id)
          ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
      ');
        $this->addSql('
          ALTER TABLE claro_import_file
          ADD CONSTRAINT FK_EA6FE9F193CB796C FOREIGN KEY (file_id)
          REFERENCES claro_public_file (id)
          ON DELETE SET NULL
      ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_import_file
        ');
    }
}
