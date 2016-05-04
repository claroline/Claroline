<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DevBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Creates a plugin. I assume you work on a linux fs.
 */
class CreatePluginCommand extends ContainerAwareCommand
{
    private $langs = array('fr', 'en', 'es');

    protected function configure()
    {
        $this->setName('claroline:plugin:create')
            ->setDescription(
                'Create a claroline plugin in your vendor directory (does not support camel case yet)'
            );
        $this->setDefinition(
            array(
                new InputArgument('vendor', InputArgument::REQUIRED, 'The vendor name'),
                new InputArgument('bundle', InputArgument::REQUIRED, 'The bundle name'),
            )
        );
        $this->addOption(
            'resource_type',
            null,
            InputOption::VALUE_REQUIRED,
            'When set to true, add a default config for the resource type'
        );
        $this->addOption(
            'tool',
            null,
            InputOption::VALUE_REQUIRED,
            'When set to true, add a default config for the tool'
        );
        $this->addOption(
            'admin_tool',
            null,
            InputOption::VALUE_REQUIRED,
            'When set to true, add a default config for the admin_tool'
        );
        $this->addOption(
            'widget',
            null,
            InputOption::VALUE_REQUIRED,
            'When set to true, add a default config for the widget'
        );
        $this->addOption(
            'external_authentication',
            null,
            InputOption::VALUE_REQUIRED,
            'When set to true, add a default external authentication for the plugin'
        );
        $this->addOption(
            'theme',
            null,
            InputOption::VALUE_REQUIRED,
            'When set to true, add a default config for the theme'
        );
        $this->addOption(
            'file_player_mime',
            null,
            InputOption::VALUE_REQUIRED,
            'When set to true, add a player for the file_player mime type'
        );
        $this->addOption(
            'install',
            'i',
            InputOption::VALUE_NONE,
            'When set to true, install the plugin in namespace and bundles.ini'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'vendor' => 'The vendor name (camel case required)',
            'bundle' => 'The bundle name (camel case required)',
        );

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument,
                    $this->askArgument(
                        $output,
                        $argumentName
                    )
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output,
            "Enter the user {$argumentName}: ",
            function ($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $vendorDir = $this->getContainer()->getParameter('claroline.param.vendor_directory');
        $skel = $this->getTemplateDirectory('skel');
        $ivendor = $input->getArgument('vendor');
        $ibundle = $input->getArgument('bundle');
        $vname = strtolower($ivendor);
        $bname = $this->getNormalizedBundleName($ibundle).'-bundle';

        //create the directories if they don't exist
        $vendorNameDir = "{$vendorDir}/{$vname}";
        $bundleNameDir = "{$vendorNameDir}/{$bname}";
        $parentDir = "{$bundleNameDir}/{$ivendor}";
        $rootDir = "{$parentDir}/{$ibundle}Bundle";
        $dirs = array($vendorNameDir, $bundleNameDir, $parentDir, $rootDir);
        $fs->mkdir($dirs);
        $this->copy($skel, $rootDir);

        $this->editBundleClass($rootDir, $ivendor, $ibundle);
        $this->editControllerClass($rootDir, $ivendor, $ibundle);
        $this->editExtensionClass($rootDir, $ivendor, $ibundle);
        $this->editComposer($rootDir, $ivendor, $ibundle);

        //now we create the resource type listener, entity & config if we wanted
        $rType = $input->getOption('resource_type');
        $tType = $input->getOption('tool');
        $wType = $input->getOption('widget');
        $eAuth = $input->getOption('external_authentication');
        $theme = $input->getOption('theme');
        $aTool = $input->getOption('admin_tool');
        $fmime = $input->getOption('file_player_mime');

        $config = array(
            'plugin' => array(
                'has_options' => false,
            ),
        );

        if ($rType) {
            $this->addResourceType($rootDir, $ivendor, $ibundle, $rType, $config);
        }
        if ($tType) {
            $this->addTool($rootDir, $ivendor, $ibundle, $tType, $config);
        }
        if ($wType) {
            $this->addWidget($rootDir, $ivendor, $ibundle, $wType, $config);
        }
        if ($eAuth) {
            $this->addAuthentication($rootDir, $ivendor, $ibundle, $eAuth, $config);
        }
        if ($theme) {
            $this->addTheme($theme, $config);
        }
        if ($aTool) {
            $this->addAdminTool($rootDir, $ivendor, $ibundle, $aTool, $config);
        }
        if ($fmime) {
            $this->addPlayer($rootDir, $ivendor, $ibundle, $fmime, $config);
        }

        $yaml = Yaml::dump($config, 5);
        file_put_contents($rootDir.'/Resources/config/config.yml', $yaml);

        $this->recursiveRenamePlaceHolders(
            $rootDir,
            $ivendor,
            $ibundle,
            $rType,
            $tType,
            $wType,
            $eAuth,
            $aTool,
            $fmime
        );

        if ($input->getOption('install')) {
            $bundleManager = $this->getContainer()->get('claroline.manager.plugin_manager');
            $bundleManager->updateIniFile($ivendor, $ibundle);
            $bundleManager->updateAutoload($ivendor, $ibundle, $vname, $bname);
        }
    }

    private function editControllerClass($rootDir, $vendor, $bundle)
    {
        $newPath = $rootDir.'/Controller/'.$bundle.'Controller.php';
        rename($rootDir.'/Controller/BundleController.tpl', $newPath);
        $content = file_get_contents($newPath);
    }

    private function editBundleClass($rootDir, $vendor, $bundle)
    {
        $newPath = $rootDir.'/'.$vendor.$bundle.'Bundle.php';
        rename($rootDir.'/VendorBundleBundle.tpl', $newPath);
        $content = file_get_contents($newPath);
    }

    private function editExtensionClass($rootDir, $vendor, $bundle)
    {
        $newPath = $rootDir.'/DependencyInjection/'.$vendor.$bundle.'Extension.php';
        rename($rootDir.'/DependencyInjection/VendorBundleExtension.tpl', $newPath);
        $content = file_get_contents($newPath);
    }

    private function editComposer($rootDir, $vendor, $bundle)
    {
        $filepath = $rootDir.'/composer.json';
        $content = file_get_contents($filepath);
        $content = str_replace('[[name]]', strtolower($vendor).'/'.$this->getNormalizedBundleName($bundle).'-bundle', $content);
        $content = str_replace('[[psr]]', $vendor.'\\\\'.$bundle.'Bundle', $content);
        $content = str_replace('[[target_dir]]', $vendor.'/'.$bundle.'Bundle', $content);
        file_put_contents($filepath, $content);
    }

    private function addResourceType($rootDir, $vendor, $bundle, $rType, &$config)
    {
        $this->addResourceTypeEntity($rootDir, $vendor, $bundle, $rType);
        $this->addResourceTypeConfig($rootDir, $vendor, $bundle, $rType, $config);
        $this->addResourceTypeForm($rootDir, $vendor, $bundle, $rType);
        $this->addResourceTypeListener($rootDir, $vendor, $bundle, $rType);
        $this->addResourceTypeRepository($rootDir, $vendor, $bundle, $rType);
        $this->addResourceTypeTranslationFiles($rootDir, $vendor, $rType);
        $transDir = $rootDir.'/Resources/translations';

        $resTrans = array(
            'fr' => array(
                'name' => 'Nom',
                'publish' => 'Publier la ressource',
            ),
            'en' => array(
                'name' => 'Name',
                'publish' => 'Publish resource',
            ),
            'es' => array(
                'name' => 'Nombre',
                'publish' => 'Publicar el recurso',
            ),
        );

        foreach ($this->langs as $lang) {
            $transFileName = $transDir.'/'.strtolower($rType).'.'.$lang.'.yml';
            file_put_contents($transFileName, Yaml::dump($resTrans[$lang], 5));
        }
    }

    private function addResourceTypeRepository($rootDir, $vendor, $bundle, $rType)
    {
        $templateDir = $this->getTemplateDirectory('optional/resource');
        $newPath = $rootDir.'/Repository/'.ucfirst($rType).'Repository.php';
        $content = file_get_contents($templateDir.'/repository.tpl');
        file_put_contents($newPath, $content);
    }

    private function addResourceTypeListener($rootDir, $vendor, $bundle, $rType)
    {
        $className = ucfirst($rType).'ResourceListener';
        $newPath = $rootDir.'/Listener/'.$className.'.php';
        $templateDir = $this->getTemplateDirectory('optional/resource');
        $content = file_get_contents($templateDir.'/listener.tpl');
        file_put_contents($newPath, $content);
    }

    private function addResourceTypeConfig($rootDir, $vendor, $bundle, $rType, &$config)
    {
        $config['plugin']['resources'][] = array(
            'class' => "{$vendor}\\{$bundle}Bundle\\Entity\\{$rType}",
            'name' => strtolower($vendor).'_'.strtolower($rType),
            'is_exportable' => false,
        );
    }

    private function addResourceTypeEntity($rootDir, $vendor, $bundle, $rType)
    {
        $templateDir = $this->getTemplateDirectory('optional/resource');
        $newPath = $rootDir.'/Entity/'.ucfirst($rType).'.php';
        $content = file_get_contents($templateDir.'/resource.tpl');
        file_put_contents($newPath, $content);
    }

    private function addResourceTypeForm($rootDir, $vendor, $bundle, $rType)
    {
        $templateDir = $this->getTemplateDirectory('optional/resource');
        $newPath = $rootDir.'/Form/'.$rType.'Type.php';
        $content = file_get_contents($templateDir.'/form.tpl');
        $viewDir = $rootDir.'/Resources/views/'.$rType;
        $fs = new Filesystem();
        $fs->mkdir($viewDir);
        file_put_contents(
            $viewDir.'/createForm.html.twig',
            file_get_contents($templateDir.'/form_view.tpl')
        );
    }

    private function addResourceTypeTranslationFiles($rootDir, $vendor, $rType)
    {
        $data = array(strtolower($vendor).'_'.strtolower($rType) => ucfirst($rType));
        $transDir = $rootDir.'/Resources/translations';

        foreach ($this->langs as $lang) {
            $transFileName = $transDir.'/resource.'.$lang.'.yml';
            file_put_contents($transFileName, Yaml::dump($data, 5));
        }
    }

    private function addTool($rootDir, $vendor, $bundle, $tType, &$config)
    {
        $this->addToolConfig($tType, $config);
        $this->addToolListener($rootDir, $vendor, $bundle, $tType);
        $this->addToolTranslationFiles($rootDir, $tType);
    }

    private function addToolConfig($rType, &$config)
    {
        $config['plugin']['tools'][] = array(
            'name' => $rType,
            'is_displayable_in_workspace' => true,
            'is_displayable_in_desktop' => true,
        );
    }

    private function addToolListener($rootDir, $vendor, $bundle, $tType)
    {
        $className = ucfirst($tType).'Listener';
        $newPath = $rootDir.'/Listener/'.$className.'.php';
        $templateDir = $this->getTemplateDirectory('optional/tool');
        $content = file_get_contents($templateDir.'/listener.tpl');
        file_put_contents($newPath, $content);
    }

    private function addToolTranslationFiles($rootDir, $tType)
    {
        $data = array(strtolower($tType) => ucfirst($tType));
        $transDir = $rootDir.'/Resources/translations';

        foreach ($this->langs as $lang) {
            $transFileName = $transDir.'/tools.'.$lang.'.yml';
            file_put_contents($transFileName, Yaml::dump($data, 5));
        }
    }

    private function addAdminTool($rootDir, $vendor, $bundle, $tType, &$config)
    {
        $this->addAdminToolConfig($tType, $config);
        $this->addAdminToolListener($rootDir, $vendor, $bundle, $tType);
        $this->addToolTranslationFiles($rootDir, $tType);
    }

    private function addAdminToolConfig($rType, &$config)
    {
        $config['plugin']['admin_tools'][] = array(
            'name' => $rType,
            'class' => 'warning',
        );
    }

    private function addAdminToolListener($rootDir, $vendor, $bundle, $tType)
    {
        $className = ucfirst($tType).'Listener';
        $newPath = $rootDir.'/Listener/'.$className.'.php';
        $templateDir = $this->getTemplateDirectory('optional/admin_tool');
        $content = file_get_contents($templateDir.'/listener.tpl');
        file_put_contents($newPath, $content);
    }

    private function addWidget($rootDir, $vendor, $bundle, $wType, &$config)
    {
        $this->addWidgetConfig($wType, $vendor, $config);
        $this->addWidgetListener($rootDir, $vendor, $bundle, $wType);
        $this->addWidgetTranslationFiles($rootDir, $vendor, $wType);
    }

    private function addWidgetConfig($wType, $vendor, &$config)
    {
        $config['plugin']['widgets'][] = array(
            'name' => strtolower($vendor).'_'.strtolower($wType).'_widget',
            'is_configurable' => false,
        );
    }

    private function addWidgetListener($rootDir, $vendor, $bundle, $wType)
    {
        $className = ucfirst($wType).'Listener';
        $newPath = $rootDir.'/Listener/'.$className.'.php';
        $templateDir = $this->getTemplateDirectory('optional/widget');
        $content = file_get_contents($templateDir.'/listener.tpl');
        file_put_contents($newPath, $content);
    }

    private function addWidgetTranslationFiles($rootDir, $vendor, $wType)
    {
        $data = array(strtolower($vendor).'_'.strtolower($wType).'_widget' => ucfirst($wType));
        $transDir = $rootDir.'/Resources/translations';

        foreach ($this->langs as $lang) {
            $transFileName = $transDir.'/widget.'.$lang.'.yml';
            file_put_contents($transFileName, Yaml::dump($data, 5));
        }
    }

    private function addAuthenticationListener($rootDir, $vendor, $bundle, $eAuth)
    {
        $newPath = $rootDir.'/Listener/ConfigureMenuListener.php';
        $templateDir = $this->getTemplateDirectory('optional/external_authentication');
        $content = file_get_contents($templateDir.'/listener.tpl');
        file_put_contents($newPath, $content);
    }

    private function addAuthenticationManager($rootDir, $vendor, $bundle, $eAuth)
    {
        $newPath = $rootDir.'/Manager/SecurityManager.php';
        $templateDir = $this->getTemplateDirectory('optional/external_authentication');
        $content = file_get_contents($templateDir.'/manager.tpl');
        file_put_contents($newPath, $content);
    }

    private function addAuthenticationController($rootDir, $vendor, $bundle, $eAuth)
    {
        $newPath = $rootDir.'/Controller/AuthenticationController.php';
        $templateDir = $this->getTemplateDirectory('optional/external_authentication');
        $content = file_get_contents($templateDir.'/controller.tpl');
        file_put_contents($newPath, $content);
        $routingFile = $this->getNewRoutingFile($rootDir);
        $addRouting = file_get_contents($templateDir.'/routing.tpl');
        if (!strpos(file_get_contents($routingFile), $addRouting)) {
            file_put_contents($routingFile, $addRouting, FILE_APPEND);
        }
    }

    private function addAuthentication($rootDir, $vendor, $bundle, $tType, &$config)
    {
        $this->addAuthenticationListener($rootDir, $vendor, $bundle, $eAuth);
        $this->addAuthenticationController($rootDir, $vendor, $bundle, $eAuth);
        $this->addAuthenticationManager($rootDir, $vendor, $bundle, $eAuth);
    }

    public function addTheme($theme, &$config)
    {
        $config['plugin']['themes'][] = array(
            'name' => $theme.' theme',
        );
    }

    public function addPlayer($rootDir, $ivendor, $ibundle, $fmime, &$config)
    {
        $className = ucfirst($fmime).'PlayerListener';
        $newPath = $rootDir.'/Listener/'.$className.'.php';
        $templateDir = $this->getTemplateDirectory('optional/player');
        $content = file_get_contents($templateDir.'/listener.tpl');
        file_put_contents($newPath, $content);

        $viewName = strtolower($fmime);
        $newPath = $rootDir.'/Resources/views/'.$viewName.'.html.twig';
        $content = file_get_contents($templateDir.'/view.tpl');
        file_put_contents($newPath, $content);
    }

    private function getNewRoutingFile($rootDir)
    {
        return $rootDir.'/Resources/config/routing.yml';
    }

    private function listFiles($source, $target, $files = array(), $rootDir = null)
    {
        if (!$rootDir) {
            $rootDir = $source;
        }
        $ds = DIRECTORY_SEPARATOR;
        $iterator = new \DirectoryIterator($source);

        foreach ($iterator as $element) {
            $newPath = $target.str_replace($rootDir, '', $element->getPathName());

            if (!$element->isDot() && $element->getBaseName() !== '.gitkeep') {
                $files[$newPath] = $element->getPathName();

                if ($element->isDir()) {
                    $files = $this->listFiles($element->getPathName(), $target, $files, $rootDir);
                }
            }
        }

        return $files;
    }

    //sf2 doesn't handle directory copies... so we copy the directory content here
    private function copy($source, $target)
    {
        $files = $this->listFiles($source, $target);

        foreach ($files as $newPath => $oldPath) {
            if (!file_exists($newPath)) {
                if (is_dir($oldPath)) {
                    mkdir($newPath, 0755, true);
                } else {
                    copy($oldPath, $newPath);
                }
            }
        }
    }

    private function recursiveRenamePlaceHolders(
        $path,
        $vendor,
        $bundle,
        $rType = null,
        $tType = null,
        $wType = null,
        $eAuth = null,
        $aTool = null,
        $fmime = null
    ) {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $el) {
            if ($el->isFile()) {
                $filepath = $el->getRealPath();
                $content = file_get_contents($filepath);
                file_put_contents(
                    $filepath,
                    $this->replaceCommonPlaceHolders($content, $vendor, $bundle, $rType, $tType, $wType, $eAuth, $aTool, $fmime)
                );
            }
        }
    }

    private function getNormalizedBundleName($ibundle)
    {
        preg_match_all('/[A-Z][^A-Z]*/', $ibundle, $results);
        $baseDirName = strtolower($results[0][0]);

        for ($i = 1; $i < count($results[0]); ++$i) {
            $baseDirName .= '-'.strtolower($results[0][$i]);
        }

        return strtolower($baseDirName);
    }

    private function replaceCommonPlaceHolders(
        $content,
        $vendor,
        $bundle,
        $rType = '',
        $tType = '',
        $wType = '',
        $eAuth = '',
        $adminTool = '',
        $fmime = ''
    ) {
        $patterns = array(
            '/\[\[Vendor\]\]/',
            '/\[\[vendor\]\]/',
            '/\[\[Bundle\]\]/',
            '/\[\[bundle\]\]/',
            '/\[\[Resource_Type\]\]/',
            '/\[\[resource_type\]\]/',
            '/\[\[Tool\]\]/',
            '/\[\[tool\]\]/',
            '/\[\[Widget\]\]/',
            '/\[\[widget\]\]/',
            '/\[\[external_authentication\]\]/',
            '/\[\[Admin_Tool\]\]/',
            '/\[\[admin_tool\]\]/',
            '/\[\[File_Mime\]\]/',
            '/\[\[file_mime\]\]/',
        );

        $replacements = array(
            ucfirst($vendor),
            strtolower($vendor),
            ucfirst($bundle),
            strtolower($bundle),
            ucfirst($rType),
            strtolower($rType),
            ucfirst($tType),
            strtolower($tType),
            ucfirst($wType),
            strtolower($wType),
            strtolower($eAuth),
            ucfirst($adminTool),
            strtolower($adminTool),
            ucfirst($fmime),
            strtolower($fmime),
        );

        return preg_replace($patterns, $replacements, $content);
    }

    private function getTemplateDirectory($subDirectory)
    {
        $vendorDir = $this->getContainer()->getParameter('claroline.param.vendor_directory');
        $baseDir = $vendorDir.'/claroline/distribution/main/dev/Resources/bundle-creator';

        return "{$baseDir}/{$subDirectory}";
    }
}
