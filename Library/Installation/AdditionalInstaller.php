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
        $this->createDatabase();
        $this->buildDefautTemplate();
    }

    private function createDatabase()
    {
        $command = new CreateDatabaseDoctrineCommand();
        $command->setContainer($this->container);
        $code = $command->run(new ArrayInput(array(), new NullOutput()));

        if ($code !== 0) {
            throw new \Exception(
                'Database cannot be created : either it already exists either '
                . 'the connection parameters or rights are incorrect'
            );
        }
    }

    private function buildDefautTemplate()
    {
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir') . '/../templates/default.zip';
        $translator = $this->container->get('translator'); // useless
        TemplateBuilder::buildDefault($defaultTemplatePath, $translator);
    }
}
