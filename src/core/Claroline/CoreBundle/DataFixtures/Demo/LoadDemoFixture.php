<?php

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\DataFixtures\Demo\LoadUserData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadGroupData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadDirectoryData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadFileData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadTextData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadWorkspaceData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadMessagesData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadActivityData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadShortcutData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadContentData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadTypeData;
use Claroline\CoreBundle\DataFixtures\Demo\LoadRegionData;;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;

class LoadDemoFixture extends LoggableFixture implements ContainerAwareInterface
{
    protected $container;
    protected $manager;
    protected $filepath;

    const NB_USERS = 20;
    const NB_GROUPS = 10;
    const USER_PER_GROUP = 5;
    const GROUP_PER_WORKSPACE = 2;
    const USER_PER_WORKSPACE = 5;

    public function __construct()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->filepath = __DIR__. "{$ds}files{$ds}";
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $start = time();

        $this->initialize($manager);

        $this->createUsers();
        $this->createGroups();
        $this->createWorkspaces();
        $this->createFilesAndDirectories();
        $this->createActivities();
        $this->createShortcuts();
        $this->createMessages();
        $this->createHomepage();

        $this->loadPluginFixtures();

        $end = time();
        $duration = $this->container->get('claroline.utilities.misc')->timeElapsed($end - $start);
        $this->log("Time elapsed for the demo creation: {$duration}");
    }

    private function initialize(ObjectManager $manager)
    {
        $this->referenceRepo = new ReferenceRepository($manager);
        $this->manager = $manager;
        $this->setReferenceRepository($this->referenceRepo);

        // add references to required (i.e. already loaded) fixtures
        $roleRepo = $manager->getRepository('ClarolineCoreBundle:Role');
        $userRole = $roleRepo->findOneByName('ROLE_USER');
        $wsCreatorRole = $roleRepo->findOneByName('ROLE_WS_CREATOR');
        $adminRole = $roleRepo->findOneByName('ROLE_ADMIN');
        $this->addReference('role/user', $userRole);
        $this->addReference('role/ws_creator', $wsCreatorRole);
        $this->addReference('role/admin', $adminRole);
    }

    private function loadFixture(AbstractFixture $fixture)
    {
        $fixture->setReferenceRepository($this->referenceRepo);

        if ($fixture instanceof ContainerAwareInterface) {
            $fixture->setContainer($this->container);
        }

        $fixture->load($this->manager);
    }

    private function createUsers()
    {
        // main users
        $this->loadFixture(
            new LoadUserData(array('John Doe' => 'admin', 'Jane Doe' => 'ws_creator'))
        );

        // random users
        $firstNames = $this->getFirstNames();
        $lastNames = $this->getLastNames();

        for ($i = 0; $i < self::NB_USERS; $i++) {
            $names[] = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
        }

        $names = array_flip(array_unique($names));
        $keys = array_keys($names);

        foreach ($keys as $key) {
            $names[$key] = 'user';
        }

        $this->loadFixture(
            new LoadUserData($names)
        );
    }

    private function createGroups()
    {
        $users = $this->manager->getRepository('ClarolineCoreBundle:User')->findAll();
        $loadGroupDataParameters = array();
        $groupClasses = $this->getGroupClass();
        $groupNames = $this->getGroupNames();

        for ($i = 0; $i < self::NB_GROUPS; $i++) {
            $names[] = $groupClasses[array_rand($groupClasses)]
                . ' - '
                . $groupNames[array_rand($groupNames)];
        }

        $names = array_unique($names);

        foreach ($names as $name) {
            $groupUsersKeys = array_rand($users, self::USER_PER_GROUP);
            $userArray = array();

            foreach ($groupUsersKeys as $key) {
                $userArray[] = $users[$key]->getFirstName() . ' ' . $users[$key]->getLastName();
            }

            $loadGroupDataParameters[$name] = $userArray;
        }

        $this->loadFixture(
            new LoadGroupData($loadGroupDataParameters)
        );
    }

    private function createWorkspaces()
    {
        $this->loadFixture(
            new LoadWorkspaceData(
                array(
                    'Cours 1' => 'Jane Doe',
                    'Cours 2' => 'Jane Doe',
                    'Cours 3' => 'Jane Doe',
                    'Cours 4' => 'Jane Doe'
                )
            )
        );
        $jane = $this->getReference('user/Jane Doe');
        $this->addUsersToWorkspace($this->getReference('workspace/Cours 1'), $jane);
        $this->addUsersToWorkspace($this->getReference('workspace/Cours 2'), $jane);
        $this->addUsersToWorkspace($this->getReference('workspace/Cours 3'), $jane);
        $this->addUsersToWorkspace($this->getReference('workspace/Cours 4'), $jane);
    }

    private function addUsersToWorkspace(AbstractWorkspace $workspace, User $excludedUser)
    {
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $users = $this->manager->getRepository('ClarolineCoreBundle:User')->findAllExcept($excludedUser);
        $groups = $this->manager->getRepository('ClarolineCoreBundle:Group')->findAll();
        $userKeys = array_rand($users, self::USER_PER_WORKSPACE);
        $groupsKey = array_rand($groups, self::GROUP_PER_WORKSPACE);
        $collaboratorRole = $this->manager
            ->getRepository('ClarolineCoreBundle:Role')
            ->findCollaboratorRole($workspace);

        foreach ($userKeys as $key) {
            $roleManager->associateRole($users[$key], $collaboratorRole);
        }

        foreach ($groupsKey as $key) {
            $roleManager->associateRole($groups[$key], $collaboratorRole);
        }
    }

    private function createFilesAndDirectories()
    {
        // John Doe
        $this->loadFixture(
            new LoadDirectoryData('John Doe', array('John Doe/Documents/Projets'))
        );
        $this->loadFixture(
            new LoadFileData('John Doe', 'Documents', array($this->filepath.'foo.txt'))
        );

        // Jane Doe
        $this->loadFixture(
            new LoadDirectoryData(
                'Jane Doe',
                array(
                    'Cours 1/Premier semestre',
                    'Cours 1/Second semestre',
                    'Cours 2/Travaux',
                    'Cours 3/Groupe 1',
                    'Cours 3/Groupe 2',
                    'Cours 3/Groupe 3',
                    'Jane Doe/Images et vidéos',
                    'Jane Doe/Docs/Activities'
                )
            )
        );
        $this->loadFixture(
            new LoadFileData('Jane Doe', 'Premier semestre', array($this->filepath.'bar.txt'))
        );
        $this->loadFixture(
            new LoadFileData('Jane Doe', 'Second semestre', array($this->filepath.'file.txt'))
        );
        $this->loadFixture(
            new LoadTextData('Jane Doe', 'Second semestre', 200, array('Infos'))
        );
        $this->loadFixture(
            new LoadTextData('Jane Doe', 'Cours 2', 200, array('Description du cours'))
        );
        $this->loadFixture(
            new LoadTextData('Jane Doe', 'Travaux', 200, array('Instructions'))
        );
        $this->loadFixture(
            new LoadFileData('Jane Doe', 'Second semestre', array($this->filepath.'claronext.odt'))
        );
        $this->loadFixture(
            new LoadFileData(
                'Jane Doe',
                'Docs',
                array(
                    $this->filepath.'lorem.pdf',
                    $this->filepath.'sample.pdf',
                    $this->filepath.'symfony.pdf'
                 )
            )
        );
        $this->loadFixture(
            new LoadFileData(
                'Jane Doe',
                'Images et vidéos',
                array(
                    $this->filepath.'video.mp4',
                    $this->filepath.'wallpaper.jpg'
                 )
            )
        );
    }

    private function createActivities()
    {
        $this->loadFixture(
            new LoadActivityData(
                'Chapitre 1',
                'Activities',
                'Jane Doe',
                array(
                    $this->getReference('file/video.mp4')->getId(),
                    $this->getReference('file/wallpaper.jpg')->getId()
                )
            )
        );
        $this->loadFixture(
            new LoadActivityData(
                'Chapitre 2',
                'Activities',
                'Jane Doe',
                array(
                    $this->getReference('file/lorem.pdf')->getId(),
                    $this->getReference('file/symfony.pdf')->getId()
                )
            )
        );
        $this->loadFixture(
            new LoadActivityData(
                'Activité',
                'Jane Doe',
                'Jane Doe',
                array(
                    $this->getReference('activity/Chapitre 1')->getId(),
                    $this->getReference('activity/Chapitre 2')->getId()
                )
            )
        );
    }

    private function createShortcuts()
    {
        $collaboratorRole = $this->manager->getRepository('ClarolineCoreBundle:Role')
            ->findCollaboratorRole($this->getReference('user/Jane Doe')->getPersonalWorkspace());
        $this->loadFixture(
            new LoadShortcutData(
                $this->getReference('directory/Docs'),
                'Activities',
                'Jane Doe'
            )
        );
        $this->loadFixture(
            new LoadShortcutData(
                $this->getReference('directory/Premier semestre'),
                'Activities',
                'Jane Doe'
            )
        );
        $this->loadFixture(
            new LoadShortcutData(
                $this->getReference('directory/Travaux'),
                'Premier semestre',
                'Jane Doe'
            )
        );
        $permissions = array(
            'canOpen' => true,
            'canDelete' => false,
            'canEdit' => false,
            'canExport' => false,
            'canCopy' => false
        );
        $rightsManager = $this->container->get('claroline.manager.rights_manager');
        $rightsManager->create(
            $permissions,
            $collaboratorRole,
            $this->getReference('directory/Docs'),
            true
        );
        $rightsManager->create(
            $permissions,
            $collaboratorRole,
            $this->getReference('directory/Premier semestre'),
            true
        );
        $rightsManager->create(
            $permissions,
            $collaboratorRole,
            $this->getReference('directory/Travaux'),
            true
        );
    }

    private function createMessages()
    {
        $this->loadFixture(
            new LoadMessagesData(
                array(
                    array('from' => 'John Doe', 'to' => 'JaneDoe', 'object' => 'Welcome !'),
                    array('to' => 'JohnDoe', 'from' => 'Jane Doe', 'object' => 'I have a problem.')
                )
            )
        );
    }

    private function createHomepage()
    {
        $this->loadFixture(new LoadTypeData());
        $this->loadFixture(new LoadContentData());
        $this->loadFixture(new LoadRegionData());
    }

    private function loadPluginFixtures()
    {
        $kernel = $this->container->get('kernel');

        if ($kernel->isClassInActiveBundle('Claroline\ForumBundle\Tests\DataFixtures\LoadForumData')) {
            $this->loadFixture(
                new LoadForumData('Forum 1', 'JaneDoe', 5, 5, $this->getReference('directory/Cours 1'))
            );
            $this->loadFixture(
                new LoadForumData('Forum doc', 'JaneDoe', 5, 5, $this->getReference('directory/Premier semestre'))
            );
        }

        if ($kernel->isClassInActiveBundle('Claroline\RssReaderBundle\Entity\Config')) {
            $rssConfig = new \Claroline\RssReaderBundle\Entity\Config();
            $rssConfig->setUrl('http://www.lesoir.be/feed/Actualit%C3%A9/Vie%20du%20net/destination_principale_block');
            $rssConfig->setDefault(true);
            $rssConfig->setDesktop(true);
            $this->manager->persist($rssConfig);
            $this->manager->flush();
        }
    }

    private function getFirstNames()
    {
        return array(
            'Mary', 'Amanda', 'James', 'Patricia', 'Michael', 'Sarah', 'Patrick', 'Homer', 'Bart', 'Marge', 'Lisa',
            'John', 'Stan', 'Stéphane', 'Emmanuel', 'Nicolas', 'Frédéric', 'Luke', 'Luc', 'Kenneth', 'Stanley',
            'Kyle', 'Léopold', 'Eric', 'Cécile', 'Marie', 'Caterine', 'Jessica', 'Matthieu', 'Aurélie', 'Elisabeth',
            'Louis', 'Jérome', 'Ned', 'Ralph', 'Charles-Montgomery',
            'Waylon', 'Carl', 'Timothy', 'Kirk', 'Milhouse', 'Todd', 'Maude', 'Benjamen', 'ObiWan', 'George',
            'Barack','Alfred', 'Paul', 'Gabriel', 'Anne', 'Théophile', 'Bill', 'Claudia', 'Silva', 'Ford',
            'Rodney', 'Greg', 'Bob', 'Robert','Jean-Kévin', 'Charles-Henry', 'Douglas', 'Arthur', 'Marvin',
            'Bruce', 'William', 'Jason', 'Mélanie', 'Sophie','Dominique', 'Coralie', 'Camille', 'Claudia',
            'Margareth', 'Antonio', 'Scarlett', 'Marie', 'Robert', 'Hélène', 'Toto','Frank',
            'Mélissa', 'Elio', 'Fabienne', 'Thomas', 'Jean-Kevin', 'Emilie', 'Marion', 'Perinne', 'Corinne',
            'Chloé'
        );
    }

    private function getLastNames()
    {
        return array(
            'Johnson', 'Miller', 'Brown', 'Williams', 'Davis', 'Simpson', 'Smith', 'Klein', 'Godfraind',
            'Gervy', 'Fervaille','Minne', 'Skywalker', 'Marsh', 'Broflovski', 'Cartman', 'Stotch', 'McCormick',
            'McLane', 'Bourne', 'Yates', 'Marilyn','McElroy', 'Flanders', 'Wiggum', 'Burns', 'Smithers',
            'Carlson', 'LoveJoy', 'VanHouten', 'Gates', 'Braconier', 'Kenobi','Lucas', 'Clooney', 'Harisson',
            'Obama', 'Bush', 'Black', 'Hogan', 'Anderson', 'McKay', 'Fields', 'Bruel', 'Kottick','Dupond',
            'Leloux', 'Miller', 'Adams', 'Dent', 'Accroc', 'Prefect', 'Escort', 'Sheridan', 'William', 'Willis',
            'Lee','Devos', 'Tatcher', 'Gilbert', 'Casilli', 'Wilson', 'Cantor', 'Descartes', 'Carlyle', 'Ford',
            'Tortelloni', 'Pizza', 'Garcia', 'Martinez', 'Thomas', 'Lefebvre', 'Fournier', 'Gauthier', 'Lemoine',
            'Bernard', 'Petit', 'Fontaine', 'Vincent', 'Henry', 'Patate', 'Tomate', 'Courgette', 'Potiron', 'Fougère',
            'Lucas', 'Henry', 'Lacroix', 'Renaud', 'Cabron'
        );
    }

    private function getGroupNames()
    {
        return array(
            'History',
            'Linguistics',
            'Literature',
            'Performing arts',
            'Philosophy',
            'Religion',
            'Visual arts',
            'Anthropology',
            'Archaeology',
            'Area studies',
            'Cultural and ethnic studies',
            'Economics',
            'Gender and sexuality',
            'Geography',
            'Political science',
            'Psychology',
            'Sociology',
            'Space science',
            'Earth sciences',
            'Life sciences',
            'Chemistry',
            'Physics',
            'Computer sciences',
            'Logic',
            'Mathematics',
            'Statistics',
            'Systems science',
            'Agriculture',
            'Architecture and Design',
            'Business', 'Education',
            'Engineering',
            'Environmental studies and Forestry',
            'Family and consumer science',
            'Health science',
            'Human physical performance and recreation',
            'Journalism, media studies and communication',
            'Law',
            'Library and museum studies',
            'Military sciences',
            'Public administration',
            'Social work',
            'Transportation'
        );
    }

    private function getGroupClass()
    {
        return array(
            'Bachelor 1', 'Bachelor 2', 'Bachelor 3', 'Master 1', 'Master 2', 'Doctorate 1', 'Doctorate 2'
        );
    }
}
