<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/02/26 01:51:16
 */
class Version20210226135116 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_log_security (
                id INT AUTO_INCREMENT NOT NULL, 
                target_id INT DEFAULT NULL, 
                doer_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                details LONGTEXT NOT NULL, 
                event VARCHAR(255) NOT NULL, 
                country VARCHAR(255) DEFAULT NULL, 
                doerIp VARCHAR(255) DEFAULT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_91F693E1158E0B66 (target_id), 
                INDEX IDX_91F693E112D3860F (doer_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_log_security 
            ADD CONSTRAINT FK_91F693E1158E0B66 FOREIGN KEY (target_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_log_security 
            ADD CONSTRAINT FK_91F693E112D3860F FOREIGN KEY (doer_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_log_security
        ');
    }
}
