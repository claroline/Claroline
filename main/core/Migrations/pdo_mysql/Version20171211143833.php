<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/12/11 02:38:34
 */
class Version20171211143833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE user_location (
                user_id INT NOT NULL,
                location_id INT NOT NULL,
                INDEX IDX_BE136DCBA76ED395 (user_id),
                INDEX IDX_BE136DCB64D218E (location_id),
                PRIMARY KEY(user_id, location_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE group_location (
                group_id INT NOT NULL,
                location_id INT NOT NULL,
                INDEX IDX_57AEC5B4FE54D947 (group_id),
                INDEX IDX_57AEC5B464D218E (location_id),
                PRIMARY KEY(group_id, location_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE user_location
            ADD CONSTRAINT FK_BE136DCBA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE user_location
            ADD CONSTRAINT FK_BE136DCB64D218E FOREIGN KEY (location_id)
            REFERENCES claro__location (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE group_location
            ADD CONSTRAINT FK_57AEC5B4FE54D947 FOREIGN KEY (group_id)
            REFERENCES claro_group (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE group_location
            ADD CONSTRAINT FK_57AEC5B464D218E FOREIGN KEY (location_id)
            REFERENCES claro__location (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            DROP TABLE claro_user_location
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_user_location (
                location_id INT NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_932BBCCB64D218E (location_id),
                INDEX IDX_932BBCCBA76ED395 (user_id),
                PRIMARY KEY(location_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_user_location
            ADD CONSTRAINT FK_932BBCCB64D218E FOREIGN KEY (location_id)
            REFERENCES claro__location (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_location
            ADD CONSTRAINT FK_932BBCCBA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            DROP TABLE user_location
        ');
        $this->addSql('
            DROP TABLE group_location
        ');
    }
}
