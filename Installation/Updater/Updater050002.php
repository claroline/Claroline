<?php
namespace Icap\PortfolioBundle\Installation\Updater;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;

class Updater050002 extends Updater
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(EntityManager $entityManager, Connection $connection)
    {
        $this->entityManager = $entityManager;
        $this->connection = $connection;
    }

    public function postUpdate()
    {
        $this->restorePortfolioTitle();
    }

    public function restorePortfolioTitle()
    {
        $totalPortfolioProcessed = 0;
        $nbPortfolioProcessed = 0;

        if ($this->connection->getSchemaManager()->tablesExist(array('icap__portfolio_widget_title'))) {
            $this->log('Restoring portfolio titles...');
            $rowPortfolioTitles = $this->connection->query('SELECT * FROM icap__portfolio_widget_title');

            foreach ($rowPortfolioTitles as $rowPortfolioTitle) {
                $abstractWidgets = $this->connection->query('SELECT aw.portfolio_id FROM icap__portfolio_abstract_widget aw WHERE id = ' . $rowPortfolioTitle['id']);
                foreach ($abstractWidgets as $abstractWidget) {
                    $this->connection->update('icap__portfolio',
                        [
                            'title' => $rowPortfolioTitle['title'],
                            'slug' => $rowPortfolioTitle['slug']
                        ],
                        [
                            'id' => $abstractWidget['portfolio_id']

                        ]);
                }

                $this->connection->delete('icap__portfolio_abstract_widget',
                    [
                        'id' => $rowPortfolioTitle['id']
                    ]);

                $nbPortfolioProcessed++;

                if ($nbPortfolioProcessed >= 10) {
                    $totalPortfolioProcessed += $nbPortfolioProcessed;
                    $nbPortfolioProcessed = 0;
                    $this->log('    processing portfolio...');
                }
            }
            $this->log(sprintf('  %d portfolio processed', $totalPortfolioProcessed + $nbPortfolioProcessed));

            $this->connection->getSchemaManager()->dropTable('icap__portfolio_widget_title');
        }
    }
}