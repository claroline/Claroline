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
        $this->restoreUserIdForAbstractWidget();
    }

    public function restorePortfolioTitle()
    {
        $totalPortfolioProcessed = 0;
        $nbPortfolioProcessed = 0;

        if ($this->connection->getSchemaManager()->tablesExist(array('icap__portfolio_widget_title'))) {
            $this->log('Restoring portfolio titles...');
            $rowPortfolioTitles = $this->connection->query('SELECT * FROM icap__portfolio_widget_title');

            foreach ($rowPortfolioTitles as $rowPortfolioTitle) {
                $rowAbstractWidgets = $this->connection->query('SELECT aw.id, aw.user_id FROM icap__portfolio_abstract_widget aw WHERE id = ' . $rowPortfolioTitle['id']);
                foreach ($rowAbstractWidgets as $rowAbstractWidget) {
                    $this->connection->update('icap__portfolio',
                        [
                            'title' => $rowPortfolioTitle['title'],
                            'slug' => $rowPortfolioTitle['slug']
                        ],
                        [
                            'id' => $rowAbstractWidget['user_id']

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

            $this->connection->delete('icap__portfolio_widget_type',
                [
                    'name' => 'title'
                ]);

            $this->connection->getSchemaManager()->dropTable('icap__portfolio_widget_title');
        }
    }

    public function restoreUserIdForAbstractWidget()
    {
        $this->log('Restoring widgets...');

        $totalWidgetProcessed = 0;
        $nbWidgetProcessed = 0;

        $rowAbstractWidgets = $this->connection->query('SELECT aw.id, aw.user_id FROM icap__portfolio_abstract_widget aw');
        foreach ($rowAbstractWidgets as $rowAbstractWidget) {
            $this->connection->query(sprintf("UPDATE icap__portfolio_abstract_widget aw
                SET aw.user_id = (
                    SELECT p.user_id
                    FROM icap__portfolio p
                    WHERE p.id = %d
                )
                WHERE aw.id = %d", $rowAbstractWidget['user_id'], $rowAbstractWidget['id']));

            $nbWidgetProcessed++;

            if ($nbWidgetProcessed >= 10) {
                $totalWidgetProcessed += $nbWidgetProcessed;
                $nbWidgetProcessed = 0;
                $this->log('    processing widget...');
            }
        }

        $this->log(sprintf('  %d widget processed', $totalWidgetProcessed + $nbWidgetProcessed));
    }
}