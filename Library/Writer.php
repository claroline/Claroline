<?php

namespace Claroline\MigrationBundle\Library;

use Twig_Environment;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Claroline\MigrationBundle\Twig\SqlFormatterExtension;

class Writer
{
    private $fileSystem;
    private $twigEnvironment;
    private $twigEngine;

    public function __construct(
        Filesystem $fileSystem,
        Twig_Environment $environment,
        TwigEngine $engine
    )
    {
        $this->fileSystem = $fileSystem;
        $this->twigEnvironment = $environment;
        $this->twigEngine = $engine;
        $this->twigEnvironment->addExtension(new SqlFormatterExtension());
    }

    public function writeMigrationClass(Bundle $bundle, $driverName, $version, array $queries)
    {
        $targetDir = "{$bundle->getPath()}/Migrations/{$driverName}";
        $class = "Version{$version}";
        $namespace = "{$bundle->getNamespace()}\\Migrations\\{$driverName}";
        $classFile = "{$targetDir}/{$class}.php";

        if (!$this->fileSystem->exists($targetDir)) {
            $this->fileSystem->mkdir($targetDir);
        }

        $content = $this->twigEngine->render(
            'ClarolineMigrationBundle::migration_class.html.twig',
            array(
                'namespace' => $namespace,
                'class' => $class,
                'upQueries' => $queries[Generator::QUERIES_UP],
                'downQueries' => $queries[Generator::QUERIES_DOWN]
            )
        );

        $this->fileSystem->touch($classFile);
        file_put_contents($classFile, $content);
    }
}
