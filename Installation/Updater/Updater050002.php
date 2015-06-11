<?php
namespace Icap\PortfolioBundle\Installation\Updater;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;

class Updater050002 extends Updater
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postUpdate()
    {
        if ($this->connection->getSchemaManager()->tablesExist(array('icap__portfolio_widget_title'))) {
            $this->log('Restoring portfolio titles...');
            $rowPortfolioTitles = $this->connection->query('SELECT * FROM icap__portfolio_widget_title');

            $this->connection->getSchemaManager()->dropTable('icap__portfolio_widget_title');
        }
    }
}