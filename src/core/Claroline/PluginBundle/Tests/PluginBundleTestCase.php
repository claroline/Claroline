<?php

namespace Claroline\PluginBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \vfsStream;

/**
 * Note : Many ways were explored to get the tests of this bundle running on fake
 *        plugin config (i.e. not the real dir/file paths used in prod environnment,
 *        which are stored as parameters in the DIC ). Here is the chosen solution
 *        (not the ideal one) :
 *        - All the test cases related to the plugin manager and its dependencies
 *          must extend this test case.
 *        - The common services (manager, validator, handlers, ...) and files are
 *          initialized with appropriate values in the set up and made available as
 *          protected attributes in the children test cases.
 *        - Plugin config files ('namespaces', 'bundles', 'routing.yml') are virtual
 *          files handled by vfsstream.
 *        - Test plugins are stored as real directory structures in the 'stub/plugin'
 *          directory.
 */
class PluginBundleTestCase extends WebTestCase
{
    /** @var \Claroline\CommonBundle\Service\Testing\TransactionalTestClient */
    protected $client;
    /** @var \Claroline\PluginBundle\Service\PluginManager\Manager*/
    protected $manager;
    /** @var \Claroline\PluginBundle\Service\PluginManager\Validator*/
    protected $validator;
    /** @var \Claroline\PluginBundle\Service\PluginManager\FileHandler */
    protected $fileHandler;
    /** @var \Claroline\PluginBundle\Service\PluginManager\DatabaseHandler */
    protected $databaseHandler;
    /** @var \Claroline\PluginBundle\Service\PluginManager\MigrationsHandler */
    protected $migrationsHandler;

    protected $pluginDirectory;
    protected $namespacesFile;
    protected $bundlesFile;
    protected $routingFile;

    public function setUp()
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();
        $this->manager = $container->get('claroline.plugin.manager');
        $this->validator = $container->get('claroline.plugin.validator');
        $this->fileHandler = $container->get('claroline.plugin.file_handler');
        $this->databaseHandler = $container->get('claroline.plugin.database_handler');
        $this->migrationsHandler = $container->get('claroline.plugin.migrations_handler');
        
        vfsStream::setup('virtual');
        $structure = array('namespaces' => '', 'bundles' => '', 'routing.yml' => '');
        vfsStream::replace($structure, 'virtual');

        $this->pluginDirectory = __DIR__ 
                . DIRECTORY_SEPARATOR
                . 'stub'
                . DIRECTORY_SEPARATOR
                . 'plugin';
        $this->namespacesFile = vfsStream::url('virtual/namespaces');
        $this->bundlesFile = vfsStream::url('virtual/bundles');
        $this->routingFile = vfsStream::url('virtual/routing.yml');

        $this->validator->setPluginDirectory($this->pluginDirectory);
        $this->fileHandler->setPluginNamespacesFile($this->namespacesFile);
        $this->fileHandler->setPluginBundlesFile($this->bundlesFile);
        $this->fileHandler->setPluginRoutingFile($this->routingFile);
    }
    
    /**
     * Helper method building a stubbed plugin.
     *
     * @param string $pluginFQCN
     * @return \Claroline\PluginBundle\Entity\AbstractPlugin the plugin object
     */
    protected function build_plugin($pluginFQCN)
    {
        $pluginFile = $this->pluginDirectory
                . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, $pluginFQCN)
                . '.php';

        require_once $pluginFile;
        return new $pluginFQCN;
    }
}