<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Optional;

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
     * @param int    $words   the numbre of words
     * @param string $creator the creator reference (without user/)
     * @param string $parent  the parent reference (without directory/)
     * @param array  $texts   an array of text name
     */
    public function __construct($creator, $parent, $words, array $texts)
    {
        $this->words = $words;
        $this->creator = $creator;
        $this->parent = $parent;
        $this->texts = $texts;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $this->getReference('user/'.$this->creator);

        foreach ($this->texts as $name) {
            $lipsumGenerator = $this->container->get('claroline.utilities.lipsum_generator');
            $revision = new Revision();
            $revision->setContent($lipsumGenerator->generateLipsum($this->words), true, 1023);
            $revision->setUser($user);
            $manager->persist($revision);
            $text = new Text();
            $text->setName($name);
            $manager->persist($text);
            $revision->setText($text);
            $text = $this->container
                ->get('claroline.manager.resource_manager')
                ->create(
                    $text,
                    $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('text'),
                    $user,
                    $this->getReference('directory/'.$this->parent)->getWorkspace(),
                    $this->getReference('directory/'.$this->parent)
                );
            $this->addReference('text/'.$name, $text);
        }
    }
}
