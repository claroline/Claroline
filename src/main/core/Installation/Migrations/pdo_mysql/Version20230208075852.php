<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/02/08 07:59:03
 */
class Version20230208075852 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_resource_evaluation SET evaluation_status = "unknown" WHERE evaluation_status IS NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE evaluation_status evaluation_status VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_resource_user_evaluation SET evaluation_status = "unknown" WHERE evaluation_status IS NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation CHANGE evaluation_status evaluation_status VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_workspace_evaluation SET evaluation_status = "unknown" WHERE evaluation_status IS NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation CHANGE evaluation_status evaluation_status VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE evaluation_status evaluation_status VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation CHANGE evaluation_status evaluation_status VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation CHANGE evaluation_status evaluation_status VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`
        ');
    }
}
