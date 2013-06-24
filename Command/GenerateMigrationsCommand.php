<?php

namespace Claroline\MigrationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\MigrationBundle\Generator\Generator;
use Claroline\MigrationBundle\Twig\SqlFormatterExtension;

class GenerateMigrationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:migrations:generate')
            ->setDescription('Creates migration classes on a per bundle basis.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating...');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $kernel = $this->getContainer()->get('kernel');
        $generator = new Generator($em);
        $queries = $generator->generateMigrationQueries($kernel->getBundle('ClarolineCoreBundle'), array());

        $migrationClass = 'Version' . time();
        $twig = $this->getContainer()->get('twig');
        $twig->addExtension(new SqlFormatterExtension());
        $templating = $this->getContainer()->get('templating');
        $content = $templating->render(
            'ClarolineMigrationBundle::migration_class.html.twig',
            array(
                'namespace' => 'Foo',
                'class' => $migrationClass,
                'upQueries' => $queries[Generator::QUERIES_UP],
                'downQueries' => $queries[Generator::QUERIES_DOWN]
            )
        );

        file_put_contents(__DIR__ . "/../Migrations/{$migrationClass}.php", $content);
    }
}