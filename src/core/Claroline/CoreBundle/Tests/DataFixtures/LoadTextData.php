<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTextData extends AbstractFixture implements ContainerAwareInterface
{
    private $texts;
    private $words;
    private $creator;
    private $parent;

    /**
     * Creates a resource text.
     *
     * @param integer $words  the numbre of words
     * @param string $creator the creator reference (without user/)
     * @param string $parent  the parent reference (without directory/)
     * @param array $texts    an array of text name
     */
    public function __construct($creator, $parent, $words, array $texts)
    {
        $this->words = $words;
        $this->creator = $creator;
        $this->parent = $parent;
        $this->texts = $texts;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $this->getReference('user/'.$this->creator);

        foreach ($this->texts as $name) {
            $lipsumGenerator = $this->container->get('claroline.utilities.lipsum_generator');
            $manager = $this->container->get('doctrine.orm.entity_manager');
            $revision = new Revision();
            $revision->setContent($lipsumGenerator->generateLipsum($this->words));
            $revision->setUser($user);
            $manager->persist($revision);
            $manager->flush();
            $text = new Text();
            $text->setName($name);
            $manager->persist($text);
            $revision->setText($text);
            $text = $this->container
                ->get('claroline.resource.manager')
                ->create($text, $this->getReference('directory/'.$this->parent)->getId(), 'text', $user);
            $this->addReference('text/'.$name, $text);
        }
    }
}