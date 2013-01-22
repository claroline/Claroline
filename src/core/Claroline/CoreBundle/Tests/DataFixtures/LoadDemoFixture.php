<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Tests\DataFixtures\Special\LoadEntitiesInWorkspace;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;

class LoadDemoFixture extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function load(ObjectManager $manager = null)
    {
        // TODO : following lines were added quickly -- we must cannot suppose the admin exists...
        $admin = $manager->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => 'admin'));
        $adminWorkspace = $admin->getPersonalWorkspace();
        $rootWorkspaceAdmin = $this->getWorkspaceRoot($adminWorkspace);
        $dir = $this->createDirectory('Documents', $rootWorkspaceAdmin, $admin);
        $this->createFile('file.txt', $dir, $admin, 'file.txt');

        $user = $this->createMainTeacher($manager);
        $this->loadFixture(new LoadUsersData(30, 'user'));
        $this->loadFixture(new LoadUsersData(10, 'ws_creator'));
        $this->loadFixture(new LoadUsersData(5, 'admin'));
        $this->loadFixture(new LoadGroupsData(20));
        $this->createDemoResources($user, $manager);
        $this->loadFixture(new LoadMessagesData(array('from' => $user->getUsername()), 20));
        $this->loadFixture(new LoadMessagesData(array('to' => $user->getUsername()), 20));
    }

    private function createMainTeacher($manager)
    {
        $user = new User();
        $user->setFirstName('Jane');
        $user->setLastName('Doe');
        $user->setUsername('teacher');
        $user->setPlainPassword('teacher');
        $roleRepo = $manager->getRepository('Claroline\CoreBundle\Entity\Role');
        $wsCreatorRole = $roleRepo->findOneByName(PlatformRoles::WS_CREATOR);
        $user->addRole($wsCreatorRole);
        $manager->persist($user);
        $user = $this->getContainer()->get('claroline.user.creator')->create($user);

        return $user;
    }

    private function loadFixture($fixture)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($em);
    }

    private function createDemoResources($user)
    {
        $accItems = array();
        $ws = $this->createWorkspace('Cours 1', $user, 'C1');
        $this->loadFixture(new LoadEntitiesInWorkspace(10, 'user', null, $ws));
        $this->loadFixture(new LoadEntitiesInWorkspace(2, 'group', null, $ws));
        $dir = $this->createDirectory('Premier semestre', $this->getWorkspaceRoot($ws), $user);
        $this->createFile('test.txt', $dir, $user, 'file.txt');
        $dir = $this->createDirectory('Second semestre', $this->getWorkspaceRoot($ws), $user);
        $this->createFile('file.txt', $dir, $user, 'file.txt');
        $this->createText('Infos', $dir, $user, 50);

        $ws = $this->createWorkspace('Cours 2', $user, 'C2');
        $this->loadFixture(new LoadEntitiesInWorkspace(10, 'user', null, $ws));
        $this->loadFixture(new LoadEntitiesInWorkspace(2, 'group', null, $ws));
        $accItems[] = $this->createText('Description du cours', $this->getWorkspaceRoot($ws), $user, 200);
        $this->createFile('Axes de Claronext.odt', $this->getWorkspaceRoot($ws), $user, 'claronext.odt');
        $this->createDirectory('Travaux', $this->getWorkspaceRoot($ws), $user);
        $this->loadFixture(new LoadForumData('Forum 1', $user->getUsername(), 5, 5, $this->getWorkspaceRoot($ws)));

        $ws = $this->createWorkspace('Cours 3', $user, 'C3');
        $this->createDirectory('Groupe 1', $this->getWorkspaceRoot($ws), $user);
        $this->createDirectory('Groupe 2', $this->getWorkspaceRoot($ws), $user);
        $this->createDirectory('Groupe 3', $this->getWorkspaceRoot($ws), $user);

        //personnalWs
        $dir = $this->createDirectory('Documents', $this->getWorkspaceRoot($user->getPersonalWorkspace()), $user);
        $this->loadFixture(new LoadEntitiesInWorkspace(10, 'user', null, $user->getPersonalWorkspace()));
        $this->loadFixture(new LoadEntitiesInWorkspace(2, 'group', null, $user->getPersonalWorkspace()));
        $accItems[] = $this->createFile('Axes de Claronext.odt', $dir, $user, 'claronext.odt');
        $accItems[] = $this->createFile('Symfony 2.0 ebook.pdf', $dir, $user, 'symfony.pdf');
        $accItems[] = $this->createFile('lorem.pdf', $dir, $user, 'lorem.pdf');
        $accItems[] = $this->createFile('sample.pdf', $dir, $user, 'sample.pdf');
        $accItems[] = $this->loadFixture(new LoadForumData('Forum Docs', $user->getUsername(), 5, 5, $dir));
        $accDir = $this->createDirectory('Activities', $dir, $user);
        $dir = $this->createDirectory(
            'Images et vidéos',
            $this->getWorkspaceRoot($user->getPersonalWorkspace()),
            $user
        );
        $accItems[] = $this->createFile('big_buck_bunny_480.webm', $dir, $user, 'bigbuck.webm');
        $accItems[] = $this->createFile('wallpaper.jpg', $dir, $user, 'wallpaper.jpg');

        //create an Activity
        $activityWs = $this->createWorkspace('Cours 4', $user, 'C4');
        $this->loadFixture(new LoadEntitiesInWorkspace(10, 'user', null, $activityWs));
        $this->loadFixture(new LoadEntitiesInWorkspace(2, 'group', null, $activityWs));

        $accItems[] = $this->createActivity('Chapitre 1', $accDir, $user, array($accItems[0], $accItems[1]));
        $accItems[] = $this->createActivity('Chapitre 2', $accDir, $user, array($accItems[2], $accItems[3]));

        $this->createActivity(
            'Activité principale',
            $this->getWorkspaceRoot($activityWs),
            $user,
            array(
                $accItems[4],
                $accItems[5],
                $accItems[6],
                $accItems[7],
                $accItems[8],
                $accItems[9]
            )
        );
    }

    private function createWorkspace($name, $user, $code)
    {
        $config = new Configuration();
        $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
        $config->setWorkspaceName($name);
        $config->setWorkspaceCode($code);
        $wsCreator = $this->getContainer()->get('claroline.workspace.creator');
        $ws = $wsCreator->createWorkspace($config, $user);

        return $ws;
    }

    private function createFile($name, $parent, $user, $fileName)
    {
        $ds = DIRECTORY_SEPARATOR;
        $filepath = __DIR__."{$ds}DemoFiles{$ds}{$fileName}";
        $fileData = new LoadFileData($name, $parent, $user, $filepath);
        $this->loadFixture($fileData);

        return $fileData->getLastFileCreated();
    }

    private function createText($name, $parent, $user, $nbWords)
    {
        $lipsumGenerator = $this->getContainer()->get('claroline.utilities.lipsum_generator');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $revision = new Revision();
        $revision->setContent($lipsumGenerator->generateLipsum($nbWords));
        $revision->setUser($user);
        $em->persist($revision);
        $em->flush();
        $text = new Text();
        $text->setLastRevision($revision);
        $text->setName($name);
        $em->persist($text);
        $revision->setText($text);
        $this->getContainer()->get('claroline.resource.manager')->create($text, $parent->getId(), 'text', $user);

        return $text;
    }

    private function createDirectory($name, $parent, $user)
    {
        $manager = $this->getContainer()->get('claroline.resource.manager');
        $directory = new Directory();
        $directory->setName($name);
        $dir = $manager->create($directory, $parent->getId(), 'directory', $user);

        return $dir;
    }

    private function getWorkspaceRoot($workspace)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $root = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findOneBy(array('workspace' => $workspace, 'parent' => null));

        return $root;
    }

    private function createActivity($name, $parent, $user, $resources)
    {
        $activity = new Activity();
        $activity->setName($name);
        $activity->setInstructions(
            $this->getContainer()
                ->get('claroline.utilities.lipsum_generator')
                ->generateLipsum(300)
        );
        $activity = $this->getContainer()
            ->get('claroline.resource.manager')
            ->create($activity, $parent->getId(), 'activity', $user);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $i = 0;

        foreach ($resources as $resource) {
            if (null != $resource) {
                $i++;
                $rs = new ResourceActivity;
                $rs->setActivity($activity);
                $rs->setResource($resource);
                $rs->setSequenceOrder($i);
                $em->persist($rs);
            }
        }

        $em->flush();

        return $activity;
    }
}