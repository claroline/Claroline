<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\UrlBundle\Transfert;

use Claroline\CoreBundle\Library\Transfert\Importer;
use HeVinci\UrlBundle\Entity\Url;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.url_importer")
 * @DI\Tag("claroline.importer")
 */
class UrlImporter extends Importer implements ConfigurationInterface
{
    /**
     * @var \HeVinci\UrlBundle\UrlManager
     */
    private $urlManager;

    private $container;
    private $om;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *      "container" = @DI\Inject("service_container"),
     *      "urlManager"= @DI\Inject("hevinci_url.manager.url")
     * })
     */
    public function __construct($om, $container, $urlManager)
    {
        $this->container = $container;
        $this->om = $om;
        $this->urlManager = $urlManager;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addUrlDescription($rootNode);

        return $treeBuilder;
    }

    public function getName()
    {
        return 'hevinci_url';
    }

    public function addUrlDescription($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('url')
                    ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data)
    {
        if (isset($data['data'])) {
            $url = new Url();
            $url->setUrl($data['data']['url']);
            $this->urlManager->setUrl($url);
            $this->om->persist($url);

            return $url;
        }
    }

    public function export($workspace, array &$files, $object)
    {
        $data = ['url' => $object->getUrl()];

        return $data;
    }
}
