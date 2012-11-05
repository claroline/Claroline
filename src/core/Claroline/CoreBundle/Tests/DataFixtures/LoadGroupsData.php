<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;;
use Claroline\CoreBundle\Entity\Group;

class LoadGroupsData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    private $nbGroups;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function __construct($nbGroups)
    {
        $this->nbGroups = $nbGroups;

        $this->basicGroupName = array(
            "History",
            "Linguistics",
            "Literature",
            "Performing arts",
            "Philosophy",
            "Religion",
            "Visual arts",
            "Anthropology",
            "Archaeology",
            "Area studies",
            "Cultural and ethnic studies",
            "Economics",
            "Gender and sexuality",
            "Geography",
            "Political science",
            "Psychology",
            "Sociology",
            "Space science",
            "Earth sciences",
            "Life sciences",
            "Chemistry",
            "Physics",
            "Computer sciences",
            "Logic",
            "Mathematics",
            "Statistics",
            "Systems science",
            "Agriculture",
            "Architecture and Design",
            "Business", "Education",
            "Engineering",
            "Environmental studies and Forestry",
            "Family and consumer science",
            "Health science",
            "Human physical performance and recreation",
            "Journalism, media studies and communication",
            "Law",
            "Library and museum studies",
            "Military sciences",
            "Public administration",
            "Social work",
            "Transportation"
        );

        $this->maxBasicGroupNameOffset = count($this->basicGroupName);
        $this->maxBasicGroupNameOffset--;

        $this->groupsYears = array(
            "Bachelor 1", "Bachelor 2", "Bachelor 3", "Master 1", "Master 2", "Doctorate 1", "Doctorate 2"
        );

        $this->maxGroupsYearsOffset = count($this->groupsYears);
        $this->maxGroupsYearsOffset--;
    }

    public function load(ObjectManager $manager)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        // Load a list of users, 100 of them is enough
        $users = $em->getRepository('ClarolineCoreBundle:User')->findBy(array(), null, 100);
        $maxUsersOffset = count($users) - 1;

        for ($i = 0; $i < $this->nbGroups; $i++) {
            // Create group
            $group = new Group();
            $group->setName($this->createGroupName());
            $manager->persist($group);
            $manager->flush();
            $this->log("Group " . ($i + 1) . " created, id=" . $group->getId() . " name='" . $group->getName() . "'");

            // Add users to group
            $userNumber = rand(1, $maxUsersOffset);
            $userAddedIds = array();
            for ($j = 0; $j <= $userNumber; $j++) {
                $created = false;
                while (false === $created) {
                    $pos = rand(0, $maxUsersOffset);
                    // Add random id in array if not already in it.
                    if (!array_key_exists($pos, $userAddedIds)) {
                        $userAddedIds[$pos] = $pos;
                        //echo "add user {$id} to group {$group->getId()}\n";
                        $group->addUser($users[$pos]);
                        $created = true;
                    }
                }
            }
            $this->log("  -> Added " . $userNumber . " users in it\n");
            $manager->persist($group);
            $manager->flush();
            // Clear EntityManager (EM) after each group to free memory and speed the EM process.
            $manager->clear();
        }
    }

    private function createGroupName()
    {
        $name = "{$this->groupsYears[mt_rand(0, $this->maxGroupsYearsOffset)]} - {$this->basicGroupName[mt_rand(0, $this->maxBasicGroupNameOffset)]} - " . mt_rand(0, 1000);

        return $name;
    }
}
