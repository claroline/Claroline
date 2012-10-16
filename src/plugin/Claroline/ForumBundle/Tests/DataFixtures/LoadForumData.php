<?php

namespace Claroline\ForumBundle\Tests\DataFixtures;

use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Message;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadForumData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

     /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = $this->getReference('user/user');
        $root = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->getRootForWorkspace($this->getReference('user/user')->getPersonalWorkspace());
        $creator = $this->container->get('claroline.resource.manager');

        $forum = new Forum();
        $forum->setName('testing forum');
        $manager->persist($forum);
        $manager->flush();
        $forumInstance = $creator->create($forum, $root->getId(), 'Forum', true, null, $user);
        $this->addReference('forum/forumInstance', $forumInstance);

    }
}
