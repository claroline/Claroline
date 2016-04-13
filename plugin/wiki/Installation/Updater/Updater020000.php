<?php

namespace Icap\WikiBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Contribution;

class Updater020000 extends Updater
{
    private $container;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
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
        $this->log('Creating active contributions for old (temporary) sections...');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $sectionRepository = $this->container->get('icap.wiki.section_repository');
        foreach ($tempSections as $tempSection) {
            $section = $sectionRepository->findOneBy(array('id' => $tempSection['id']));
            $user = $em->getReference('ClarolineCoreBundle:User', $tempSection['user_id']);
            $activeContribution = new Contribution();
            $activeContribution->setTitle($tempSection['title']);
            $activeContribution->setText($tempSection['text']);
            $activeContribution->setCreationDate(new \DateTime($tempSection['creation_date']));
            $activeContribution->setSection($section);
            $activeContribution->setContributor($user);

            $section->setActiveContribution($activeContribution);

            $em->persist($section);
            $em->flush();
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
}
