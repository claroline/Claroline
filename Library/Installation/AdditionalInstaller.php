<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;

class AdditionalInstaller extends ContainerAware
{
    public function preInstall()
    {
        $this->createDatabaseIfNotExists();
        $this->buildDefaultTemplate();
    }

    private function createDatabaseIfNotExists()
    {
        try {
            $this->container->get('doctrine.dbal.default_connection');
        } catch (\Exception $ex) {
            $command = new CreateDatabaseDoctrineCommand();
            $command->setContainer($this->container);
            $code = $command->run(new ArrayInput(array()), new NullOutput());

            if ($code !== 0) {
                throw new \Exception(
                    'Database doesn\'t exist and cannot be created : check that the parameters '
                    . 'you provided are correct and/or that you have sufficient permissions.'
                );
            }
        }
    }

    private function buildDefaultTemplate()
    {
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir') . '/../templates/default.zip';
        $translator = $this->container->get('translator'); // useless
        TemplateBuilder::buildDefault($defaultTemplatePath, $translator);
    }
}
