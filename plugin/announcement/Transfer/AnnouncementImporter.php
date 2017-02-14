<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Transfer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.importer.announcement_importer")
 * @DI\Tag("claroline.importer")
 */
class AnnouncementImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    private $container;
    private $om;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "container"    = @DI\Inject("service_container"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct($om, $container, TokenStorageInterface $tokenStorage)
    {
        $this->container = $container;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addAnnouncementDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'claroline_announcement_aggregate';
    }

    public function addAnnouncementDescription($rootNode)
    {
        $rootPath = $this->getRootPath();
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('announcement')
                        ->children()
                            ->scalarNode('title')->end()
                            ->scalarNode('announcer')->end()
                            ->scalarNode('creation_date')->end()
                            ->scalarNode('publication_date')->end()
                            ->booleanNode('visible')->end()
                            ->scalarNode('visible_from')->end()
                            ->scalarNode('visible_until')->end()
                            ->arrayNode('content')
                                ->children()
                                    ->scalarNode('path')->isRequired()
                                        ->validate()
                                            ->ifTrue(
                                                function ($v) use ($rootPath) {
                                                    return call_user_func_array(
                                                        __CLASS__.'::fileNotExists',
                                                        [$v, $rootPath]
                                                    );
                                                }
                                            )
                                            ->thenInvalid("The file %s doesn't exists")
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function supports($type)
    {
        return $type === 'yml';
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public static function fileNotExists($v, $rootpath)
    {
        $ds = DIRECTORY_SEPARATOR;

        return !file_exists($rootpath.$ds.$v);
    }

    public function import(array $data)
    {
        $announcementAggregate = new AnnouncementAggregate();
        $user = $this->tokenStorage->getToken()->getUser();
        $ds = DIRECTORY_SEPARATOR;

        if (isset($data['data'])) {
            foreach ($data['data'] as $announcementDatas) {
                $announcement = new Announcement();
                $announcement->setAggregate($announcementAggregate);
                $announcement->setCreator($user);
                $announcement->setTitle($announcementDatas['announcement']['title']);
                $announcement->setAnnouncer($announcementDatas['announcement']['announcer']);
                $announcement->setVisible($announcementDatas['announcement']['visible']);

                if ($announcementDatas['announcement']['creation_date'] !== null) {
                    $announcement->setCreationDate(new \DateTime($announcementDatas['announcement']['creation_date']));
                } else {
                    $announcement->setPublicationDate(new \DateTime());
                }
                if ($announcementDatas['announcement']['publication_date'] !== null) {
                    $announcement->setPublicationDate(new \DateTime($announcementDatas['announcement']['publication_date']));
                }
                if ($announcementDatas['announcement']['visible_from'] !== null) {
                    $announcement->setVisibleFrom(new \DateTime($announcementDatas['announcement']['visible_from']));
                }
                if ($announcementDatas['announcement']['visible_until'] !== null) {
                    $announcement->setVisibleUntil(new \DateTime($announcementDatas['announcement']['visible_until']));
                }
                $content = file_get_contents($this->getRootPath().$ds.$announcementDatas['announcement']['content']['path']);
                $content = !empty($content) ? $content : 'No content';
                $announcement->setContent($content);
                $this->om->persist($announcement);
            }
        }
        $this->om->persist($announcementAggregate);

        return $announcementAggregate;
    }

    public function export($workspace, array &$files, $object)
    {
        $data = [];
        $announcements = $object->getAnnouncements();

        foreach ($announcements as $announcement) {
            $content = $announcement->getContent();
            $uid = uniqid().'.txt';
            $tmpPath = $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir')
                .DIRECTORY_SEPARATOR.$uid;
            file_put_contents($tmpPath, $content);
            $files[$uid] = $tmpPath;
            $data[] = [
                'announcement' => [
                    'title' => $announcement->getTitle(),
                    'announcer' => $announcement->getAnnouncer(),
                    'creation_date' => $announcement->getCreationDate() ? $announcement->getCreationDate()->format('Y-m-d H:i:s') : null,
                    'publication_date' => $announcement->getPublicationDate() ? $announcement->getPublicationDate()->format('Y-m-d H:i:s') : null,
                    'visible' => $announcement->isVisible(),
                    'visible_from' => $announcement->getVisibleFrom() ? $announcement->getVisibleFrom()->format('Y-m-d') : null,
                    'visible_until' => $announcement->getVisibleUntil() ? $announcement->getVisibleUntil()->format('Y-m-d') : null,
                    'content' => ['path' => $uid],
                ],
            ];
        }

        return $data;
    }

    public function format($data)
    {
        foreach ($data as $d) {
            if (isset($d['announcement']['content']['path'])) {
                $path = $d['announcement']['content']['path'];
                $content = file_get_contents($this->getRootPath().DIRECTORY_SEPARATOR.$path);
                $entities = $this->om->getRepository('ClarolineAnnouncementBundle:Announcement')->findByContent($content);

                foreach ($entities as $entity) {
                    $text = $entity->getContent();
                    $text = $this->container->get('claroline.importer.rich_text_formatter')->format($text);
                    $entity->setContent($text);
                    $this->om->persist($entity);
                }
            }
        }
    }
}
