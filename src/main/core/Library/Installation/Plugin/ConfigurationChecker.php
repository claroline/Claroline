<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Parser;

/**
 * Checker used to validate the configuration file of a plugin.
 */
class ConfigurationChecker implements CheckerInterface
{
    /** @var Parser */
    private $yamlParser;

    /** @var EntityManager */
    private $em;

    private $processedConfiguration;

    public function __construct(Parser $yamlParser, EntityManager $em)
    {
        $this->yamlParser = $yamlParser;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Create dedicated repository methods to retrieve tool/type names
     */
    public function check(PluginBundleInterface $plugin, $updateMode = false)
    {
        if (!is_file($plugin->getConfigFile())) {
            $error = new ValidationError('config.yml file missing');
            $errors = [$error];

            return $errors;
        }

        $config = $this->yamlParser->parse(file_get_contents($plugin->getConfigFile()));
        $names = [];

        //required for update to claroline v10 because database not updated yet from older version
        try {
            $listResource = $this->em
                ->getRepository(ResourceType::class)
                ->findAll();
        } catch (\Exception $e) {
            $listResource = [];
        }

        foreach ($listResource as $resource) {
            $names[] = $resource->getName();
        }

        $tools = [];
        /** @var Tool[] $listTool */
        $listTool = $this->em
            ->getRepository(Tool::class)
            ->findAll();

        foreach ($listTool as $tool) {
            $toolPlugin = $tool->getPlugin();

            $tools[] = sprintf('%s%s', ($toolPlugin ? $toolPlugin->getBundleFQCN().'-' : ''), $tool->getName());
        }

        $resourceActions = [];

        //required for update to claroline v10 because database not updated yet from older version
        try {
            $listResourceActions = $this->em
                ->getRepository(MenuAction::class)
                ->findBy(['resourceType' => null, 'isCustom' => true]);
        } catch (\Exception $e) {
            $listResourceActions = [];
        }

        foreach ($listResourceActions as $resourceAction) {
            $resourceActions[] = $resourceAction->getName();
        }

        $widgets = [];
        /** @var Widget[] $listWidget */
        $listWidget = $this->em
            ->getRepository(Widget::class)
            ->findAll();

        foreach ($listWidget as $widget) {
            $widgetPlugin = $widget->getPlugin();

            $widgets[] = sprintf('%s%s', ($widgetPlugin ? $widgetPlugin->getBundleFQCN().'-' : ''), $widget->getName());
        }

        $processor = new Processor();
        $configuration = new Configuration($plugin, $names, $tools, $resourceActions, $widgets);
        $configuration->setUpdateMode($updateMode);

        try {
            $this->processedConfiguration = $processor->processConfiguration($configuration, $config);
        } catch (\Exception $e) {
            $error = new ValidationError($e->getMessage());

            return [$error];
        }

        return [];
    }

    public function getProcessedConfiguration()
    {
        return $this->processedConfiguration;
    }
}
