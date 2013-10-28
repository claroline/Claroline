<?php

namespace Icap\WikiBundle\Installation\Updater;

use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Contribution;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Symfony\Component\Filesystem\Filesystem;

class Updater020000
{
    private $container;
    private $conn;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function preUpdate()
    {
        $this->copyWikiSectionTable();
    }

    public function postUpdate()
    {
        // this one isn't specific to 2.0 update
        $tempSections = $this->getTempSections();
        $this->createActiveContributions($tempSections);

        $this->dropTables();
    }

    private function getTempSections()
    {
        try {
            $this->log('Retrieving temporary sections...');
            return $this->conn->query('SELECT * FROM icap__wiki_section_temp');
        } catch (\Exception $e) {
            $this->log('Error while retrieving temporary sections');
        }        
    }

    private function createActiveContributions($tempSections)
    {
        try {
            $this->log('Creating active contributions for old (temporary) sections...');
            foreach ($tempSections as $tempSection) {
                $insertQuery = "INSERT INTO icap__wiki_contribution ('title', 'text', 'creation_date', 'user_id', 'section_id')
                          VALUES ({$tempSection['title']}, {$tempSection['text']}, {$tempSection['creation_date']}, {$tempSection['user_id']}, {$tempSection['id']})";
                $this->conn->query($insertQuery);

                $activeContributionId = $this->conn->query("SELECT id FROM icap__wiki_contribution WHERE section_id = {$tempSection['id']}")[0];
                $updateQuery = "UPDATE icap__wiki_section SET active_contribution_id = {$activeContributionId['id']} WHERE id = {$tempSection['id']}";
                $this->conn->query($updateQuery);
            }
        } catch (\Exception $e) {
            $this->log('An Exception has been thrown during the creation of contributions');
        }
    }

    private function copyWikiSectionTable()
    {
        $this->log('Copying wiki section table to a temporaty table...');
        $this->conn->query('
            CREATE TABLE icap__wiki_section_temp
            AS (SELECT * FROM icap__wiki_section)
        ');
    }    

    private function dropTables()
    {
        $this->log('Dropping outdated and temporary tables...');
        $this->conn->query('DROP table icap__wiki_section_temp');
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}
