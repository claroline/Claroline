<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/09/08 09:00:49
 */
class Version20210908090043 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD event_class VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_planned_object SET event_class = "Claroline\\\AgendaBundle\\\Entity\\\Task" WHERE event_type = "task"
        ');

        $this->addSql('
            UPDATE claro_planned_object SET event_class = "Claroline\\\AgendaBundle\\\Entity\\\Event" WHERE event_type = "event"
        ');

        $this->addSql('
            UPDATE claro_planned_object SET event_class = "Claroline\\\CursusBundle\\\Entity\\\Event" WHERE event_type = "training_event"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP event_class
        ');
    }
}
